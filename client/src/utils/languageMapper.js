const LANGUAGE_MAPPINGS = [
  { pattern: /python/i, monaco: 'python' },
  { pattern: /javascript|node\.?js/i, monaco: 'javascript' },
  { pattern: /typescript/i, monaco: 'typescript' },
  { pattern: /c\+\+|cpp/i, monaco: 'cpp' },
  { pattern: /^c\s*\(|^c$/i, monaco: 'c' },
  { pattern: /c#|csharp/i, monaco: 'csharp' },
  { pattern: /java(?!script)/i, monaco: 'java' },
  { pattern: /ruby/i, monaco: 'ruby' },
  { pattern: /go\s*\(/i, monaco: 'go' },
  { pattern: /rust/i, monaco: 'rust' },
  { pattern: /swift/i, monaco: 'swift' },
  { pattern: /kotlin/i, monaco: 'kotlin' },
  { pattern: /scala/i, monaco: 'scala' },
  { pattern: /php/i, monaco: 'php' },
  { pattern: /perl/i, monaco: 'perl' },
  { pattern: /r\s*\(/i, monaco: 'r' },
  { pattern: /lua/i, monaco: 'lua' },
  { pattern: /haskell/i, monaco: 'haskell' },
  { pattern: /clojure/i, monaco: 'clojure' },
  { pattern: /elixir/i, monaco: 'elixir' },
  { pattern: /erlang/i, monaco: 'erlang' },
  { pattern: /f#|fsharp/i, monaco: 'fsharp' },
  { pattern: /fortran/i, monaco: 'fortran' },
  { pattern: /groovy/i, monaco: 'groovy' },
  { pattern: /objective-?c/i, monaco: 'objective-c' },
  { pattern: /pascal/i, monaco: 'pascal' },
  { pattern: /prolog/i, monaco: 'prolog' },
  { pattern: /sql/i, monaco: 'sql' },
  { pattern: /bash|shell/i, monaco: 'shell' },
  { pattern: /powershell/i, monaco: 'powershell' },
  { pattern: /assembly|nasm/i, monaco: 'asm' },
  { pattern: /cobol/i, monaco: 'cobol' },
  { pattern: /d\s*\(/i, monaco: 'd' },
  { pattern: /dart/i, monaco: 'dart' },
  { pattern: /lisp|common lisp/i, monaco: 'lisp' },
  { pattern: /scheme/i, monaco: 'scheme' },
  { pattern: /ocaml/i, monaco: 'ocaml' },
  { pattern: /vb\.?net|visual basic/i, monaco: 'vb' },
  { pattern: /coffeescript/i, monaco: 'coffeescript' },
  { pattern: /nim/i, monaco: 'nim' },
  { pattern: /crystal/i, monaco: 'crystal' },
  { pattern: /zig/i, monaco: 'zig' },
  { pattern: /julia/i, monaco: 'julia' },
  { pattern: /racket/i, monaco: 'racket' },
  { pattern: /ada/i, monaco: 'ada' },
  { pattern: /apex/i, monaco: 'apex' },
  { pattern: /basic/i, monaco: 'basic' },
  { pattern: /brainfuck/i, monaco: 'brainfuck' },
  { pattern: /text|plain/i, monaco: 'plaintext' }
];

export function mapLanguageToMonaco(judge0Name) {
  if (!judge0Name) return 'plaintext';
  
  for (const mapping of LANGUAGE_MAPPINGS) {
    if (mapping.pattern.test(judge0Name)) {
      return mapping.monaco;
    }
  }
  
  return 'plaintext';
}

export function getDefaultCode(monacoLanguage) {
  const defaults = {
    python: 'print("Hello World")',
    javascript: 'console.log("Hello World");',
    typescript: 'console.log("Hello World");',
    cpp: '#include <iostream>\n\nint main() {\n    std::cout << "Hello World" << std::endl;\n    return 0;\n}',
    c: '#include <stdio.h>\n\nint main() {\n    printf("Hello World\\n");\n    return 0;\n}',
    csharp: 'using System;\n\nclass Program {\n    static void Main() {\n        Console.WriteLine("Hello World");\n    }\n}',
    java: 'public class Main {\n    public static void main(String[] args) {\n        System.out.println("Hello World");\n    }\n}',
    ruby: 'puts "Hello World"',
    go: 'package main\n\nimport "fmt"\n\nfunc main() {\n    fmt.Println("Hello World")\n}',
    rust: 'fn main() {\n    println!("Hello World");\n}',
    swift: 'print("Hello World")',
    kotlin: 'fun main() {\n    println("Hello World")\n}',
    scala: 'object Main extends App {\n    println("Hello World")\n}',
    php: '<?php\necho "Hello World\\n";\n?>',
    perl: 'print "Hello World\\n";',
    shell: 'echo "Hello World"',
    lua: 'print("Hello World")',
    haskell: 'main = putStrLn "Hello World"',
    r: 'print("Hello World")',
    julia: 'println("Hello World")',
    dart: 'void main() {\n    print("Hello World");\n}',
    elixir: 'IO.puts "Hello World"',
    erlang: '-module(main).\n-export([main/0]).\n\nmain() ->\n    io:fwrite("Hello World~n").',
    fsharp: 'printfn "Hello World"',
    ocaml: 'print_endline "Hello World";;',
    clojure: '(println "Hello World")',
    lisp: '(print "Hello World")',
    scheme: '(display "Hello World")\n(newline)',
    prolog: ':- initialization(main).\nmain :- write(\'Hello World\'), nl.',
    pascal: 'program HelloWorld;\nbegin\n    writeln(\'Hello World\');\nend.',
    fortran: 'program hello\n    print *, "Hello World"\nend program hello',
    cobol: '       IDENTIFICATION DIVISION.\n       PROGRAM-ID. HELLO.\n       PROCEDURE DIVISION.\n           DISPLAY "Hello World".\n           STOP RUN.',
    ada: 'with Ada.Text_IO; use Ada.Text_IO;\nprocedure Hello is\nbegin\n   Put_Line ("Hello World");\nend Hello;',
    vb: 'Module Module1\n    Sub Main()\n        Console.WriteLine("Hello World")\n    End Sub\nEnd Module',
    asm: 'section .data\n    msg db "Hello World", 10\n    len equ $ - msg\n\nsection .text\n    global _start\n\n_start:\n    mov eax, 4\n    mov ebx, 1\n    mov ecx, msg\n    mov edx, len\n    int 0x80\n    mov eax, 1\n    xor ebx, ebx\n    int 0x80'
  };
  
  return defaults[monacoLanguage] || '// Enter your code here';
}
