"""
Sign Language Translation (TCR) - Main Training Script
Using How2Sign Dataset from Kaggle

This script implements all 6 steps of the execution plan:
1. Download How2Sign from Kaggle
2. Inspect Data Structure
3. Data Alignment
4. Preprocessing
5. Model Architecture (TCRModel)
6. Training Loop

Usage in Colab:
    !git clone https://github.com/01fe23bcs183/nimisha-sign
    %cd nimisha-sign
    !pip install torch torchvision nltk sacrebleu kaggle -q
    from how2sign_tcr.main import run_experiment
    run_experiment()
"""

import os
import json
import glob
import re
from pathlib import Path
from collections import Counter
from typing import Dict, List, Tuple, Optional

import torch
import torch.nn as nn
import torch.nn.functional as F
from torch.utils.data import Dataset, DataLoader

# Try to import optional dependencies
try:
    import nltk
    from nltk.translate.bleu_score import corpus_bleu, SmoothingFunction
    nltk.download('punkt', quiet=True)
    NLTK_AVAILABLE = True
except ImportError:
    NLTK_AVAILABLE = False
    print("Warning: nltk not available, BLEU scores will be approximate")


# ============================================================================
# STEP 1: Download How2Sign Dataset from Kaggle
# ============================================================================

def setup_kaggle_credentials(username: str, api_key: str) -> None:
    """Set up Kaggle API credentials."""
    kaggle_dir = Path.home() / '.kaggle'
    kaggle_dir.mkdir(exist_ok=True)
    
    kaggle_json = kaggle_dir / 'kaggle.json'
    with open(kaggle_json, 'w') as f:
        json.dump({"username": username, "key": api_key}, f)
    
    os.chmod(kaggle_json, 0o600)
    print(f"Kaggle credentials saved to {kaggle_json}")


def download_dataset(data_dir: str = "/content/data") -> str:
    """
    Download How2Sign dataset from Kaggle.
    
    Args:
        data_dir: Directory to save the dataset
        
    Returns:
        Path to the downloaded dataset
    """
    os.makedirs(data_dir, exist_ok=True)
    
    # Try to download using kaggle CLI
    try:
        import subprocess
        result = subprocess.run(
            ["kaggle", "datasets", "download", "-d", "nazarboholii/how2sign", 
             "-p", data_dir, "--unzip"],
            capture_output=True, text=True
        )
        if result.returncode == 0:
            print(f"Dataset downloaded to {data_dir}")
        else:
            print(f"Kaggle download failed: {result.stderr}")
            print("Note: The Kaggle dataset may be unavailable. Using synthetic data for testing.")
            return create_synthetic_data(data_dir)
    except Exception as e:
        print(f"Error downloading dataset: {e}")
        print("Using synthetic data for testing.")
        return create_synthetic_data(data_dir)
    
    return data_dir


def create_synthetic_data(data_dir: str) -> str:
    """
    Create synthetic data for testing when real dataset is unavailable.
    
    This creates mock OpenPose keypoint files and a translations CSV
    to validate the model architecture and training loop.
    """
    import random
    
    print("Creating synthetic How2Sign data for testing...")
    
    # Create directories
    keypoints_dir = Path(data_dir) / "keypoints"
    keypoints_dir.mkdir(parents=True, exist_ok=True)
    
    # Sample translations
    translations = [
        "hello how are you",
        "my name is john",
        "nice to meet you",
        "thank you very much",
        "goodbye see you later",
        "what is your name",
        "i am fine thank you",
        "please help me",
        "where is the bathroom",
        "i love you",
    ]
    
    # Create synthetic keypoint files and CSV
    csv_lines = ["SENTENCE_NAME,SENTENCE\n"]
    
    for i, text in enumerate(translations):
        video_id = f"video_{i:04d}"
        
        # Create synthetic OpenPose keypoint file
        # OpenPose format: 25 body keypoints, 21 hand keypoints each, 70 face keypoints
        num_frames = random.randint(50, 150)
        keypoint_data = {
            "video_id": video_id,
            "frames": []
        }
        
        for frame_idx in range(num_frames):
            frame_data = {
                "frame_id": frame_idx,
                "people": [{
                    # Body: 25 keypoints * 3 (x, y, confidence)
                    "pose_keypoints_2d": [random.random() for _ in range(75)],
                    # Left hand: 21 keypoints * 3
                    "hand_left_keypoints_2d": [random.random() for _ in range(63)],
                    # Right hand: 21 keypoints * 3
                    "hand_right_keypoints_2d": [random.random() for _ in range(63)],
                    # Face: 70 keypoints * 3
                    "face_keypoints_2d": [random.random() for _ in range(210)],
                }]
            }
            keypoint_data["frames"].append(frame_data)
        
        # Save keypoint file
        keypoint_file = keypoints_dir / f"{video_id}_openpose.json"
        with open(keypoint_file, 'w') as f:
            json.dump(keypoint_data, f)
        
        # Add to CSV
        csv_lines.append(f"{video_id},{text}\n")
    
    # Save translations CSV
    csv_file = Path(data_dir) / "translations.csv"
    with open(csv_file, 'w') as f:
        f.writelines(csv_lines)
    
    # Verify size
    total_size = sum(f.stat().st_size for f in Path(data_dir).rglob('*') if f.is_file())
    print(f"Synthetic data created: {total_size / 1024:.2f} KB")
    print(f"  - {len(translations)} video samples")
    print(f"  - Keypoints: {keypoints_dir}")
    print(f"  - Translations: {csv_file}")
    
    return data_dir


def verify_download(data_dir: str) -> Dict:
    """Verify the downloaded dataset and return statistics."""
    data_path = Path(data_dir)
    
    if not data_path.exists():
        return {"error": "Data directory does not exist"}
    
    # Calculate total size
    total_size = sum(f.stat().st_size for f in data_path.rglob('*') if f.is_file())
    
    # Count files by type
    json_files = list(data_path.rglob('*.json'))
    csv_files = list(data_path.rglob('*.csv'))
    npy_files = list(data_path.rglob('*.npy'))
    
    stats = {
        "total_size_mb": total_size / (1024 * 1024),
        "json_files": len(json_files),
        "csv_files": len(csv_files),
        "npy_files": len(npy_files),
        "is_valid": total_size > 1000  # At least 1KB for synthetic data
    }
    
    print(f"Dataset verification:")
    print(f"  Total size: {stats['total_size_mb']:.2f} MB")
    print(f"  JSON files: {stats['json_files']}")
    print(f"  CSV files: {stats['csv_files']}")
    print(f"  NPY files: {stats['npy_files']}")
    
    return stats


# ============================================================================
# STEP 2: Inspect Data Structure
# ============================================================================

def inspect_keypoint_file(file_path: str) -> Dict:
    """
    Open one keypoint JSON file and print first 10 lines to verify OpenPose data.
    """
    with open(file_path, 'r') as f:
        data = json.load(f)
    
    print(f"\nInspecting keypoint file: {file_path}")
    print(f"Keys: {list(data.keys())}")
    
    if "frames" in data:
        print(f"Number of frames: {len(data['frames'])}")
        if len(data['frames']) > 0:
            first_frame = data['frames'][0]
            print(f"First frame keys: {list(first_frame.keys())}")
            if "people" in first_frame and len(first_frame['people']) > 0:
                person = first_frame['people'][0]
                print(f"Person keys: {list(person.keys())}")
                for key in person:
                    if isinstance(person[key], list):
                        print(f"  {key}: {len(person[key])} values")
    
    return data


def inspect_translations_csv(file_path: str) -> Tuple[List[str], int]:
    """
    Open the CSV file and check column names for text translations.
    Returns the column names and vocabulary size.
    """
    import csv
    
    with open(file_path, 'r') as f:
        reader = csv.reader(f)
        headers = next(reader)
        
        print(f"\nInspecting translations CSV: {file_path}")
        print(f"Column names: {headers}")
        
        # Collect all words for vocabulary
        all_words = []
        sentence_count = 0
        
        for row in reader:
            sentence_count += 1
            # Assume last column is the translation
            if len(row) > 0:
                text = row[-1].lower()
                words = text.split()
                all_words.extend(words)
        
        vocab = set(all_words)
        print(f"Number of sentences: {sentence_count}")
        print(f"Vocabulary size: {len(vocab)}")
        print(f"Sample words: {list(vocab)[:10]}")
        
        return headers, len(vocab)


def get_vocabulary(data_dir: str) -> Tuple[Dict[str, int], Dict[int, str]]:
    """
    Build vocabulary from translations.
    Returns word2idx and idx2word mappings.
    """
    csv_files = list(Path(data_dir).rglob('*.csv'))
    
    if not csv_files:
        print("No CSV files found, using default vocabulary")
        # Default vocabulary for testing
        words = ["<pad>", "<sos>", "<eos>", "<unk>", 
                 "hello", "how", "are", "you", "my", "name", "is",
                 "thank", "please", "help", "me", "goodbye", "see",
                 "later", "what", "your", "i", "am", "fine", "nice",
                 "to", "meet", "love", "where", "the", "bathroom",
                 "john", "very", "much"]
    else:
        import csv
        words = ["<pad>", "<sos>", "<eos>", "<unk>"]
        
        for csv_file in csv_files:
            with open(csv_file, 'r') as f:
                reader = csv.reader(f)
                next(reader)  # Skip header
                for row in reader:
                    if len(row) > 0:
                        text = row[-1].lower()
                        words.extend(text.split())
        
        words = ["<pad>", "<sos>", "<eos>", "<unk>"] + list(set(words[4:]))
    
    word2idx = {w: i for i, w in enumerate(words)}
    idx2word = {i: w for i, w in enumerate(words)}
    
    print(f"Vocabulary built: {len(word2idx)} words")
    
    return word2idx, idx2word


# ============================================================================
# STEP 3: Data Alignment
# ============================================================================

def canonicalize_id(filename: str) -> str:
    """
    Match keypoint filenames to CSV IDs.
    Strip extensions (.json, .npy) and suffixes (_openpose, _front, _panoptic).
    
    Examples:
        video_0001_openpose.json -> video_0001
        video_0001_front.npy -> video_0001
        video_0001_panoptic.json -> video_0001
    """
    # Remove extension
    name = Path(filename).stem
    
    # Remove common suffixes
    suffixes_to_remove = ['_openpose', '_front', '_panoptic', '_keypoints', '_pose']
    for suffix in suffixes_to_remove:
        if name.endswith(suffix):
            name = name[:-len(suffix)]
    
    return name


def align_data(data_dir: str) -> List[Dict]:
    """
    Align keypoint files with translations.
    Returns list of matched samples with paths and translations.
    """
    import csv
    
    data_path = Path(data_dir)
    
    # Load translations
    translations = {}
    csv_files = list(data_path.rglob('*.csv'))
    
    for csv_file in csv_files:
        with open(csv_file, 'r') as f:
            reader = csv.reader(f)
            headers = next(reader)
            for row in reader:
                if len(row) >= 2:
                    video_id = canonicalize_id(row[0])
                    translation = row[-1]
                    translations[video_id] = translation
    
    # Find keypoint files
    keypoint_files = list(data_path.rglob('*.json'))
    
    # Match files
    matched = []
    for kp_file in keypoint_files:
        video_id = canonicalize_id(kp_file.name)
        if video_id in translations:
            matched.append({
                "video_id": video_id,
                "keypoint_file": str(kp_file),
                "translation": translations[video_id]
            })
    
    print(f"\nData Alignment:")
    print(f"  Total keypoint files: {len(keypoint_files)}")
    print(f"  Total translations: {len(translations)}")
    print(f"  Matched: {len(matched)} files out of {len(keypoint_files)}")
    
    return matched


# ============================================================================
# STEP 4: Preprocessing
# ============================================================================

MAX_LEN = 256  # Maximum sequence length for videos
MAX_TEXT_LEN = 50  # Maximum text length

# Keypoint dimensions
BODY_KEYPOINTS = 25
HAND_KEYPOINTS = 21
FACE_KEYPOINTS = 70


def extract_keypoints(keypoint_file: str) -> Tuple[torch.Tensor, torch.Tensor, torch.Tensor]:
    """
    Extract (x,y) coordinates for Face, Hands, Body from JSON.
    
    Returns:
        body: Tensor of shape [num_frames, 25*2]
        hands: Tensor of shape [num_frames, 42*2] (left + right)
        face: Tensor of shape [num_frames, 70*2]
    """
    with open(keypoint_file, 'r') as f:
        data = json.load(f)
    
    frames = data.get('frames', [])
    num_frames = len(frames)
    
    body_list = []
    hands_list = []
    face_list = []
    
    for frame in frames:
        people = frame.get('people', [])
        if len(people) > 0:
            person = people[0]
            
            # Body keypoints: extract x,y (skip confidence)
            pose = person.get('pose_keypoints_2d', [0] * 75)
            body_xy = [pose[i] for i in range(len(pose)) if i % 3 != 2][:BODY_KEYPOINTS*2]
            body_xy = body_xy + [0] * (BODY_KEYPOINTS*2 - len(body_xy))
            
            # Hand keypoints
            left_hand = person.get('hand_left_keypoints_2d', [0] * 63)
            right_hand = person.get('hand_right_keypoints_2d', [0] * 63)
            left_xy = [left_hand[i] for i in range(len(left_hand)) if i % 3 != 2][:HAND_KEYPOINTS*2]
            right_xy = [right_hand[i] for i in range(len(right_hand)) if i % 3 != 2][:HAND_KEYPOINTS*2]
            left_xy = left_xy + [0] * (HAND_KEYPOINTS*2 - len(left_xy))
            right_xy = right_xy + [0] * (HAND_KEYPOINTS*2 - len(right_xy))
            hands_xy = left_xy + right_xy
            
            # Face keypoints
            face = person.get('face_keypoints_2d', [0] * 210)
            face_xy = [face[i] for i in range(len(face)) if i % 3 != 2][:FACE_KEYPOINTS*2]
            face_xy = face_xy + [0] * (FACE_KEYPOINTS*2 - len(face_xy))
            
        else:
            body_xy = [0] * (BODY_KEYPOINTS * 2)
            hands_xy = [0] * (HAND_KEYPOINTS * 2 * 2)
            face_xy = [0] * (FACE_KEYPOINTS * 2)
        
        body_list.append(body_xy)
        hands_list.append(hands_xy)
        face_list.append(face_xy)
    
    body = torch.tensor(body_list, dtype=torch.float32)
    hands = torch.tensor(hands_list, dtype=torch.float32)
    face = torch.tensor(face_list, dtype=torch.float32)
    
    return body, hands, face


def pad_or_truncate(tensor: torch.Tensor, max_len: int) -> Tuple[torch.Tensor, torch.Tensor]:
    """
    Pad or truncate tensor to max_len.
    Returns padded tensor and padding mask.
    """
    seq_len = tensor.shape[0]
    
    if seq_len > max_len:
        # Truncate
        tensor = tensor[:max_len]
        mask = torch.ones(max_len, dtype=torch.bool)
    else:
        # Pad
        pad_len = max_len - seq_len
        padding = torch.zeros(pad_len, tensor.shape[1], dtype=tensor.dtype)
        tensor = torch.cat([tensor, padding], dim=0)
        mask = torch.cat([torch.ones(seq_len, dtype=torch.bool), 
                         torch.zeros(pad_len, dtype=torch.bool)])
    
    return tensor, mask


def tokenize_text(text: str, word2idx: Dict[str, int], max_len: int = MAX_TEXT_LEN) -> Tuple[torch.Tensor, torch.Tensor]:
    """
    Tokenize text and convert to indices.
    Returns token indices and mask.
    """
    words = ["<sos>"] + text.lower().split() + ["<eos>"]
    indices = [word2idx.get(w, word2idx["<unk>"]) for w in words]
    
    if len(indices) > max_len:
        indices = indices[:max_len]
        mask = torch.ones(max_len, dtype=torch.bool)
    else:
        pad_len = max_len - len(indices)
        mask = torch.cat([torch.ones(len(indices), dtype=torch.bool),
                         torch.zeros(pad_len, dtype=torch.bool)])
        indices = indices + [word2idx["<pad>"]] * pad_len
    
    return torch.tensor(indices, dtype=torch.long), mask


class How2SignDataset(Dataset):
    """PyTorch Dataset for How2Sign data."""
    
    def __init__(self, samples: List[Dict], word2idx: Dict[str, int], 
                 max_video_len: int = MAX_LEN, max_text_len: int = MAX_TEXT_LEN):
        self.samples = samples
        self.word2idx = word2idx
        self.max_video_len = max_video_len
        self.max_text_len = max_text_len
    
    def __len__(self):
        return len(self.samples)
    
    def __getitem__(self, idx):
        sample = self.samples[idx]
        
        # Extract keypoints
        body, hands, face = extract_keypoints(sample['keypoint_file'])
        
        # Pad/truncate
        body, body_mask = pad_or_truncate(body, self.max_video_len)
        hands, _ = pad_or_truncate(hands, self.max_video_len)
        face, _ = pad_or_truncate(face, self.max_video_len)
        
        # Tokenize text
        text_indices, text_mask = tokenize_text(
            sample['translation'], self.word2idx, self.max_text_len
        )
        
        return {
            'body': body,
            'hands': hands,
            'face': face,
            'video_mask': body_mask,
            'text': text_indices,
            'text_mask': text_mask,
            'translation': sample['translation']
        }


def preprocess_and_save(data_dir: str, output_file: str = "train_data.pt") -> str:
    """
    Preprocess all data and save to a single file.
    """
    # Get vocabulary
    word2idx, idx2word = get_vocabulary(data_dir)
    
    # Align data
    samples = align_data(data_dir)
    
    if len(samples) == 0:
        print("No samples found!")
        return None
    
    # Create dataset
    dataset = How2SignDataset(samples, word2idx)
    
    # Process all samples
    all_data = {
        'body': [],
        'hands': [],
        'face': [],
        'video_mask': [],
        'text': [],
        'text_mask': [],
        'translations': [],
        'word2idx': word2idx,
        'idx2word': idx2word
    }
    
    print(f"\nPreprocessing {len(dataset)} samples...")
    for i in range(len(dataset)):
        sample = dataset[i]
        all_data['body'].append(sample['body'])
        all_data['hands'].append(sample['hands'])
        all_data['face'].append(sample['face'])
        all_data['video_mask'].append(sample['video_mask'])
        all_data['text'].append(sample['text'])
        all_data['text_mask'].append(sample['text_mask'])
        all_data['translations'].append(sample['translation'])
    
    # Stack tensors
    all_data['body'] = torch.stack(all_data['body'])
    all_data['hands'] = torch.stack(all_data['hands'])
    all_data['face'] = torch.stack(all_data['face'])
    all_data['video_mask'] = torch.stack(all_data['video_mask'])
    all_data['text'] = torch.stack(all_data['text'])
    all_data['text_mask'] = torch.stack(all_data['text_mask'])
    
    # Save
    output_path = Path(data_dir) / output_file
    torch.save(all_data, output_path)
    
    print(f"Preprocessed data saved to {output_path}")
    print(f"  Body shape: {all_data['body'].shape}")
    print(f"  Hands shape: {all_data['hands'].shape}")
    print(f"  Face shape: {all_data['face'].shape}")
    print(f"  Text shape: {all_data['text'].shape}")
    
    return str(output_path)


# ============================================================================
# STEP 5: Model Architecture (TCRModel)
# ============================================================================

class CNN1DEncoder(nn.Module):
    """1D CNN Encoder for temporal sequences."""
    
    def __init__(self, input_dim: int, hidden_dim: int = 256, num_layers: int = 3):
        super().__init__()
        
        layers = []
        in_channels = input_dim
        
        for i in range(num_layers):
            out_channels = hidden_dim
            layers.extend([
                nn.Conv1d(in_channels, out_channels, kernel_size=3, padding=1),
                nn.BatchNorm1d(out_channels),
                nn.ReLU(),
                nn.Dropout(0.1)
            ])
            in_channels = out_channels
        
        self.encoder = nn.Sequential(*layers)
        self.output_dim = hidden_dim
    
    def forward(self, x: torch.Tensor) -> torch.Tensor:
        # x: [batch, seq_len, features]
        x = x.transpose(1, 2)  # [batch, features, seq_len]
        x = self.encoder(x)
        x = x.transpose(1, 2)  # [batch, seq_len, hidden]
        return x


class TCRModule(nn.Module):
    """
    Temporal Cross-modal Representation Module.
    Combines body/hands stream with face stream using cross-attention.
    """
    
    def __init__(self, hidden_dim: int = 256, num_heads: int = 8):
        super().__init__()
        
        self.cross_attention = nn.MultiheadAttention(
            embed_dim=hidden_dim, num_heads=num_heads, batch_first=True
        )
        
        self.temporal_conv = nn.Conv1d(hidden_dim, hidden_dim, kernel_size=3, padding=1)
        self.layer_norm = nn.LayerNorm(hidden_dim)
        self.dropout = nn.Dropout(0.1)
    
    def forward(self, body_hands: torch.Tensor, face: torch.Tensor, 
                mask: Optional[torch.Tensor] = None) -> Tuple[torch.Tensor, torch.Tensor]:
        """
        Args:
            body_hands: [batch, seq_len, hidden]
            face: [batch, seq_len, hidden]
            mask: [batch, seq_len] padding mask
            
        Returns:
            fused: Cross-modal fused representation
            tcr_loss: Temporal consistency regularization loss
        """
        # Cross-modal attention: body/hands attend to face
        key_padding_mask = ~mask if mask is not None else None
        
        attended, _ = self.cross_attention(
            query=body_hands, key=face, value=face,
            key_padding_mask=key_padding_mask
        )
        
        # Residual connection
        fused = self.layer_norm(body_hands + self.dropout(attended))
        
        # Temporal consistency loss
        # Encourage smooth temporal transitions
        fused_t = fused.transpose(1, 2)  # [batch, hidden, seq_len]
        temporal_diff = fused_t[:, :, 1:] - fused_t[:, :, :-1]
        tcr_loss = torch.mean(temporal_diff ** 2)
        
        return fused, tcr_loss


class TransformerDecoder(nn.Module):
    """Transformer Decoder for text generation."""
    
    def __init__(self, vocab_size: int, hidden_dim: int = 256, 
                 num_layers: int = 4, num_heads: int = 8, max_len: int = MAX_TEXT_LEN):
        super().__init__()
        
        self.embedding = nn.Embedding(vocab_size, hidden_dim)
        self.pos_embedding = nn.Embedding(max_len, hidden_dim)
        
        decoder_layer = nn.TransformerDecoderLayer(
            d_model=hidden_dim, nhead=num_heads, batch_first=True
        )
        self.decoder = nn.TransformerDecoder(decoder_layer, num_layers=num_layers)
        
        self.output_proj = nn.Linear(hidden_dim, vocab_size)
        self.hidden_dim = hidden_dim
        self.max_len = max_len
    
    def forward(self, tgt: torch.Tensor, memory: torch.Tensor,
                tgt_mask: Optional[torch.Tensor] = None,
                memory_mask: Optional[torch.Tensor] = None) -> torch.Tensor:
        """
        Args:
            tgt: Target token indices [batch, tgt_len]
            memory: Encoder output [batch, src_len, hidden]
            tgt_mask: Target attention mask
            memory_mask: Memory padding mask
            
        Returns:
            logits: [batch, tgt_len, vocab_size]
        """
        batch_size, tgt_len = tgt.shape
        
        # Embeddings
        positions = torch.arange(tgt_len, device=tgt.device).unsqueeze(0).expand(batch_size, -1)
        x = self.embedding(tgt) + self.pos_embedding(positions)
        
        # Causal mask for autoregressive decoding
        causal_mask = nn.Transformer.generate_square_subsequent_mask(tgt_len, device=tgt.device)
        
        # Decode
        x = self.decoder(
            tgt=x, memory=memory,
            tgt_mask=causal_mask,
            memory_key_padding_mask=memory_mask
        )
        
        logits = self.output_proj(x)
        return logits
    
    def generate(self, memory: torch.Tensor, memory_mask: torch.Tensor,
                 sos_idx: int, eos_idx: int, max_len: int = None) -> torch.Tensor:
        """Generate text autoregressively."""
        if max_len is None:
            max_len = self.max_len
        
        batch_size = memory.shape[0]
        device = memory.device
        
        # Start with SOS token
        generated = torch.full((batch_size, 1), sos_idx, dtype=torch.long, device=device)
        
        for _ in range(max_len - 1):
            logits = self.forward(generated, memory, memory_mask=~memory_mask)
            next_token = logits[:, -1, :].argmax(dim=-1, keepdim=True)
            generated = torch.cat([generated, next_token], dim=1)
            
            # Stop if all sequences have generated EOS
            if (next_token == eos_idx).all():
                break
        
        return generated


class TCRModel(nn.Module):
    """
    Complete TCR Model for Sign Language Translation.
    
    Architecture:
        - Stream A: 1D-CNN Encoder for Body/Hands
        - Stream B: 1D-CNN Encoder for Face
        - TCR Module: Cross-modal + temporal consistency
        - Transformer Decoder for text generation
    """
    
    def __init__(self, vocab_size: int, hidden_dim: int = 256,
                 body_dim: int = BODY_KEYPOINTS * 2,
                 hands_dim: int = HAND_KEYPOINTS * 2 * 2,
                 face_dim: int = FACE_KEYPOINTS * 2):
        super().__init__()
        
        self.vocab_size = vocab_size
        self.hidden_dim = hidden_dim
        
        # Stream A: Body + Hands encoder
        self.body_hands_encoder = CNN1DEncoder(
            input_dim=body_dim + hands_dim, hidden_dim=hidden_dim
        )
        
        # Stream B: Face encoder
        self.face_encoder = CNN1DEncoder(
            input_dim=face_dim, hidden_dim=hidden_dim
        )
        
        # TCR Module
        self.tcr = TCRModule(hidden_dim=hidden_dim)
        
        # Transformer Decoder
        self.decoder = TransformerDecoder(
            vocab_size=vocab_size, hidden_dim=hidden_dim
        )
    
    def encode(self, body: torch.Tensor, hands: torch.Tensor, 
               face: torch.Tensor, mask: torch.Tensor) -> Tuple[torch.Tensor, torch.Tensor]:
        """
        Encode video features.
        
        Returns:
            memory: Encoded features for decoder
            tcr_loss: Temporal consistency loss
        """
        # Concatenate body and hands
        body_hands = torch.cat([body, hands], dim=-1)
        
        # Encode streams
        body_hands_enc = self.body_hands_encoder(body_hands)
        face_enc = self.face_encoder(face)
        
        # Cross-modal fusion with TCR
        memory, tcr_loss = self.tcr(body_hands_enc, face_enc, mask)
        
        return memory, tcr_loss
    
    def forward(self, body: torch.Tensor, hands: torch.Tensor,
                face: torch.Tensor, video_mask: torch.Tensor,
                text: torch.Tensor) -> Tuple[torch.Tensor, torch.Tensor]:
        """
        Forward pass for training.
        
        Returns:
            logits: [batch, text_len, vocab_size]
            tcr_loss: Temporal consistency loss
        """
        memory, tcr_loss = self.encode(body, hands, face, video_mask)
        
        # Decoder (teacher forcing)
        logits = self.decoder(text[:, :-1], memory, memory_mask=~video_mask)
        
        return logits, tcr_loss
    
    def generate(self, body: torch.Tensor, hands: torch.Tensor,
                 face: torch.Tensor, video_mask: torch.Tensor,
                 sos_idx: int, eos_idx: int) -> torch.Tensor:
        """Generate translations."""
        memory, _ = self.encode(body, hands, face, video_mask)
        return self.decoder.generate(memory, video_mask, sos_idx, eos_idx)


# ============================================================================
# STEP 6: Training Loop
# ============================================================================

def compute_bleu(predictions: List[str], references: List[str]) -> float:
    """Compute BLEU score."""
    if not NLTK_AVAILABLE:
        # Simple word overlap metric as fallback
        total_overlap = 0
        for pred, ref in zip(predictions, references):
            pred_words = set(pred.lower().split())
            ref_words = set(ref.lower().split())
            if len(ref_words) > 0:
                overlap = len(pred_words & ref_words) / len(ref_words)
                total_overlap += overlap
        return total_overlap / len(predictions) if predictions else 0.0
    
    # NLTK BLEU
    refs = [[ref.lower().split()] for ref in references]
    hyps = [pred.lower().split() for pred in predictions]
    
    smoothing = SmoothingFunction().method1
    try:
        score = corpus_bleu(refs, hyps, smoothing_function=smoothing)
    except Exception:
        score = 0.0
    
    return score


def decode_tokens(indices: torch.Tensor, idx2word: Dict[int, str]) -> str:
    """Convert token indices to text."""
    words = []
    for idx in indices.tolist():
        word = idx2word.get(idx, "<unk>")
        if word == "<eos>":
            break
        if word not in ["<pad>", "<sos>"]:
            words.append(word)
    return " ".join(words)


def train_epoch(model: TCRModel, dataloader: DataLoader, optimizer: torch.optim.Optimizer,
                criterion: nn.Module, device: torch.device, tcr_weight: float = 0.1) -> float:
    """Train for one epoch."""
    model.train()
    total_loss = 0
    
    for batch in dataloader:
        body = batch['body'].to(device)
        hands = batch['hands'].to(device)
        face = batch['face'].to(device)
        video_mask = batch['video_mask'].to(device)
        text = batch['text'].to(device)
        
        optimizer.zero_grad()
        
        logits, tcr_loss = model(body, hands, face, video_mask, text)
        
        # Cross-entropy loss
        ce_loss = criterion(
            logits.reshape(-1, model.vocab_size),
            text[:, 1:].reshape(-1)
        )
        
        # Total loss
        loss = ce_loss + tcr_weight * tcr_loss
        
        loss.backward()
        torch.nn.utils.clip_grad_norm_(model.parameters(), max_norm=1.0)
        optimizer.step()
        
        total_loss += loss.item()
    
    return total_loss / len(dataloader)


def evaluate(model: TCRModel, dataloader: DataLoader, idx2word: Dict[int, str],
             device: torch.device, sos_idx: int, eos_idx: int) -> Tuple[float, List[str], List[str]]:
    """Evaluate model and compute BLEU score."""
    model.eval()
    
    all_predictions = []
    all_references = []
    
    with torch.no_grad():
        for batch in dataloader:
            body = batch['body'].to(device)
            hands = batch['hands'].to(device)
            face = batch['face'].to(device)
            video_mask = batch['video_mask'].to(device)
            references = batch['translation']
            
            # Generate
            generated = model.generate(body, hands, face, video_mask, sos_idx, eos_idx)
            
            # Decode
            for i in range(generated.shape[0]):
                pred_text = decode_tokens(generated[i], idx2word)
                all_predictions.append(pred_text)
                all_references.append(references[i])
    
    bleu = compute_bleu(all_predictions, all_references)
    
    return bleu, all_predictions, all_references


def train(model: TCRModel, train_loader: DataLoader, val_loader: DataLoader,
          word2idx: Dict[str, int], idx2word: Dict[int, str],
          device: torch.device, num_epochs: int = 5, lr: float = 1e-4) -> Dict:
    """
    Main training loop.
    
    Trains for specified epochs and reports Loss and BLEU after each epoch.
    """
    optimizer = torch.optim.AdamW(model.parameters(), lr=lr)
    criterion = nn.CrossEntropyLoss(ignore_index=word2idx["<pad>"])
    
    sos_idx = word2idx["<sos>"]
    eos_idx = word2idx["<eos>"]
    
    history = {"train_loss": [], "val_bleu": []}
    
    print(f"\n{'='*60}")
    print(f"Starting Training: {num_epochs} epochs")
    print(f"Device: {device}")
    print(f"{'='*60}\n")
    
    for epoch in range(num_epochs):
        # Train
        train_loss = train_epoch(model, train_loader, optimizer, criterion, device)
        
        # Evaluate
        val_bleu, predictions, references = evaluate(
            model, val_loader, idx2word, device, sos_idx, eos_idx
        )
        
        history["train_loss"].append(train_loss)
        history["val_bleu"].append(val_bleu)
        
        print(f"Epoch {epoch+1}/{num_epochs}")
        print(f"  Loss: {train_loss:.4f}")
        print(f"  BLEU: {val_bleu:.4f}")
        
        # Show sample predictions
        if len(predictions) > 0:
            print(f"  Sample prediction: '{predictions[0]}'")
            print(f"  Reference: '{references[0]}'")
        print()
    
    print(f"{'='*60}")
    print("Training Complete!")
    print(f"Final Loss: {history['train_loss'][-1]:.4f}")
    print(f"Final BLEU: {history['val_bleu'][-1]:.4f}")
    print(f"{'='*60}")
    
    return history


# ============================================================================
# Main Experiment Runner
# ============================================================================

def run_experiment(data_dir: str = "/content/data", 
                   kaggle_username: str = None,
                   kaggle_key: str = None,
                   num_epochs: int = 5,
                   batch_size: int = 4,
                   use_synthetic: bool = False) -> Dict:
    """
    Run the complete Sign Language Translation experiment.
    
    This function executes all 6 steps of the execution plan:
    1. Download How2Sign from Kaggle
    2. Inspect Data Structure
    3. Data Alignment
    4. Preprocessing
    5. Model Architecture
    6. Training Loop
    
    Args:
        data_dir: Directory for data storage
        kaggle_username: Kaggle username (optional)
        kaggle_key: Kaggle API key (optional)
        num_epochs: Number of training epochs
        batch_size: Batch size for training
        use_synthetic: Force use of synthetic data
        
    Returns:
        Dictionary with training history and model info
    """
    print("="*60)
    print("Sign Language Translation (TCR) Experiment")
    print("="*60)
    
    # Determine device
    if torch.cuda.is_available():
        device = torch.device("cuda")
        print(f"Using GPU: {torch.cuda.get_device_name(0)}")
    else:
        device = torch.device("cpu")
        print("Using CPU (training will be slower)")
    
    # STEP 1: Download dataset
    print("\n" + "="*60)
    print("STEP 1: Download How2Sign Dataset")
    print("="*60)
    
    if use_synthetic:
        data_dir = create_synthetic_data(data_dir)
    else:
        if kaggle_username and kaggle_key:
            setup_kaggle_credentials(kaggle_username, kaggle_key)
        data_dir = download_dataset(data_dir)
    
    stats = verify_download(data_dir)
    
    # STEP 2: Inspect data structure
    print("\n" + "="*60)
    print("STEP 2: Inspect Data Structure")
    print("="*60)
    
    json_files = list(Path(data_dir).rglob('*.json'))
    if json_files:
        inspect_keypoint_file(str(json_files[0]))
    
    csv_files = list(Path(data_dir).rglob('*.csv'))
    if csv_files:
        inspect_translations_csv(str(csv_files[0]))
    
    word2idx, idx2word = get_vocabulary(data_dir)
    vocab_size = len(word2idx)
    print(f"\nVocabulary size: {vocab_size}")
    
    # STEP 3: Data Alignment
    print("\n" + "="*60)
    print("STEP 3: Data Alignment")
    print("="*60)
    
    samples = align_data(data_dir)
    
    # STEP 4: Preprocessing
    print("\n" + "="*60)
    print("STEP 4: Preprocessing")
    print("="*60)
    
    # Create dataset
    dataset = How2SignDataset(samples, word2idx, max_video_len=MAX_LEN)
    
    # Split into train/val
    train_size = int(0.8 * len(dataset))
    val_size = len(dataset) - train_size
    train_dataset, val_dataset = torch.utils.data.random_split(
        dataset, [train_size, val_size]
    )
    
    train_loader = DataLoader(train_dataset, batch_size=batch_size, shuffle=True)
    val_loader = DataLoader(val_dataset, batch_size=batch_size, shuffle=False)
    
    print(f"Train samples: {len(train_dataset)}")
    print(f"Val samples: {len(val_dataset)}")
    
    # STEP 5: Model Architecture
    print("\n" + "="*60)
    print("STEP 5: Build TCRModel Architecture")
    print("="*60)
    
    model = TCRModel(vocab_size=vocab_size, hidden_dim=256)
    model = model.to(device)
    
    total_params = sum(p.numel() for p in model.parameters())
    trainable_params = sum(p.numel() for p in model.parameters() if p.requires_grad)
    print(f"Total parameters: {total_params:,}")
    print(f"Trainable parameters: {trainable_params:,}")
    
    # STEP 6: Training
    print("\n" + "="*60)
    print("STEP 6: Training Loop")
    print("="*60)
    
    history = train(
        model=model,
        train_loader=train_loader,
        val_loader=val_loader,
        word2idx=word2idx,
        idx2word=idx2word,
        device=device,
        num_epochs=num_epochs
    )
    
    return {
        "history": history,
        "vocab_size": vocab_size,
        "model_params": total_params,
        "device": str(device)
    }


if __name__ == "__main__":
    # Run with synthetic data for testing
    results = run_experiment(use_synthetic=True, num_epochs=5, batch_size=4)
    print("\nExperiment Results:")
    print(f"  Final Loss: {results['history']['train_loss'][-1]:.4f}")
    print(f"  Final BLEU: {results['history']['val_bleu'][-1]:.4f}")
