# Sign Language Translation (TCR) Project - Session Snapshot

## Project Overview
Building a Sign Language Translation model with Temporal Consistency Regularization (TCR) on Google Colab TPU using the How2Sign dataset.

## Completed Work (Synthetic Data - Needs Real Data)

### Step 1: TPU Initialization
- Installed torch-xla and cloud-tpu-client
- Verified TPU detection using `xm.get_xla_supported_devices()` returns `['xla:0']`
- TPU device: xla:0

### Step 2: Data Acquisition (SYNTHETIC - NEEDS REPLACEMENT)
- Created 10 synthetic sample videos with random keypoints
- Implemented `canonicalize_id()` function for filename matching:
```python
def canonicalize_id(filename):
    name = Path(filename).stem
    suffixes_to_remove = ['_openpose', '_front', '_panoptic', '_keypoints']
    for suffix in suffixes_to_remove:
        if suffix in name:
            name = name.split(suffix)[0]
    if '_frame' in name:
        name = name.split('_frame')[0]
    return name.lower()
```

### Step 3: Preprocessing Pipeline
- Extracted keypoints from JSON files
- Implemented `pad_or_truncate()` for fixed-length sequences (MAX_LEN=256)
- Created tensors: Body [10, 256, 50], Face [10, 256, 140], Hands [10, 256, 84], Mask [10, 256]
- Saved to /content/processed_data/train_data.pt

### Step 4: Model Architecture (TCRModel)
Implemented complete model with:
- **PositionalEncoding**: Transformer positional encoding
- **StreamEncoder**: 1D-CNN with 3 conv layers for encoding streams
- **TCRModule**: Cross-modal consistency loss (MSE) + temporal smoothness loss
- **TCRModel**: Main class with encode/decode methods
  - body_dim=50, hands_dim=84, face_dim=140
  - hidden_dim=256, vocab_size=1000 (NEEDS UPDATE for real data)
  - num_decoder_layers=4, num_heads=8
- **SimpleTokenizer**: Basic tokenizer for text encoding/decoding

### Step 5: Training Loop
- Used torch_xla for TPU optimization
- Used `xm.optimizer_step(optimizer)` for weight updates
- Trained for 5 epochs with decreasing loss:
  - Epoch 1: Total Loss 24.08, CE Loss 6.61, TCR Loss 174.77
  - Epoch 5: Total Loss 5.89, CE Loss 4.45, TCR Loss 14.43

## What Needs to Change for Real Data

### Data Source
- **Kaggle Dataset**: https://www.kaggle.com/datasets/nazarboholii/how2sign
- Download command: `kaggle datasets download -d nazarboholii/how2sign`
- Verify download size is 1GB+

### Code Changes Required
1. **Vocabulary Size**: Update from 1000 to actual vocabulary size (~16,000+ words)
2. **Data Paths**: Update to match Kaggle dataset structure
3. **Tokenizer**: Rebuild vocabulary from real CSV translations
4. **Sequence Length**: Verify MAX_LEN=256 is appropriate for real videos

### Key Files in Colab
- `/content/How2Sign/` - Dataset directory (after Kaggle download)
- `/content/processed_data/train_data.pt` - Processed tensors
- Model classes defined in notebook cells

## Google Drive Links (BROKEN - 404 Errors)
The official How2Sign Google Drive links are no longer working:
- train_2D_keypoints.tar.gz (ID: 1TBX7hLraMiiLucknM1mhblNVomO9-Y0r) - 404
- val_2D_keypoints.tar.gz (ID: 1JmEsU0GYUD5iVdefMOZpeWa_iYnmK_7w) - 404
- test_2D_keypoints.tar.gz (ID: 1g8tzzW5BNPzHXlamuMQOvdwlHRa-29Vp) - 404

Use Kaggle instead.

## Model Code Reference

### TCRModel Architecture
```python
class TCRModel(nn.Module):
    def __init__(self, body_dim=50, hands_dim=84, face_dim=140, 
                 hidden_dim=256, vocab_size=1000, max_text_len=64,
                 num_decoder_layers=4, num_heads=8):
        # Stream A: Body + Hands encoder
        self.body_hands_encoder = StreamEncoder(body_dim + hands_dim, hidden_dim, hidden_dim)
        # Stream B: Face encoder
        self.face_encoder = StreamEncoder(face_dim, hidden_dim, hidden_dim)
        # TCR Module
        self.tcr_module = TCRModule(hidden_dim)
        # Fusion + Decoder
        self.fusion = nn.Linear(hidden_dim * 2, hidden_dim)
        self.decoder = nn.TransformerDecoder(...)
```

### Training Loop Pattern
```python
for epoch in range(NUM_EPOCHS):
    for batch in dataloader:
        body, hands, face, mask, tgt = [x.to(device) for x in batch]
        logits, tcr_loss = model(body, hands, face, tgt[:, :-1], mask)
        ce_loss = criterion(logits.reshape(-1, vocab_size), tgt[:, 1:].reshape(-1))
        loss = ce_loss + 0.1 * tcr_loss
        loss.backward()
        xm.optimizer_step(optimizer)
        xm.mark_step()
```

## Session Info
- Devin Session: https://app.devin.ai/sessions/eb786ee991b3454bb1370804b54ab2ab
- User: Jeevan H (iamjeevanh@gmail.com)
- GitHub: @01fe23bcs183
- Repository: 01fe23bcs183/nimisha-sign
