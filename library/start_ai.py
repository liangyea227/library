"""
start_ai.py  -  Lim Library AI Services Launcher
==================================================
Start BOTH AI services with ONE command:

    python start_ai.py

What this does:
    - Starts AI Chatbot       on http://localhost:5000
    - Starts Image Search API on http://localhost:5001
    - Shows colour-coded logs from both in one terminal
    - Auto-restarts either service if it crashes
    - Press Ctrl+C once to stop both cleanly

Requirements before running:
    1. XAMPP Apache + MySQL must be running
    2. Ollama must be running  (https://ollama.com)
    3. pip install -r requirements.txt  (done once)
"""

import subprocess
import sys
import os
import signal
import threading
import time

# ---------------------------------------------------------------------------
# Script paths  (relative to this file)
# ---------------------------------------------------------------------------
BASE      = os.path.dirname(os.path.abspath(__file__))
CHATBOT   = os.path.join(BASE, "chatbot",      "chatbot.py")
IMGSEARCH = os.path.join(BASE, "image_search", "image_search_api.py")

# ---------------------------------------------------------------------------
# Console colours
# ---------------------------------------------------------------------------
CYAN   = "\033[96m"
YELLOW = "\033[93m"
RED    = "\033[91m"
GREEN  = "\033[92m"
RESET  = "\033[0m"

# Global list of running processes
processes = []


def stream_output(proc, label, colour):
    """Read a subprocess stdout and print each line with a colour prefix."""
    try:
        for line in iter(proc.stdout.readline, b""):
            text = line.decode(errors="replace").rstrip()
            if text:
                print(f"{colour}[{label}]{RESET} {text}", flush=True)
    except Exception:
        pass


def start_service(script_path, label, colour):
    """Launch a Python script as a background subprocess."""
    if not os.path.exists(script_path):
        print(f"{RED}[ERROR]{RESET} Script not found: {script_path}")
        return None

    proc = subprocess.Popen(
        [sys.executable, script_path],
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
        cwd=os.path.dirname(script_path),
    )
    t = threading.Thread(
        target=stream_output,
        args=(proc, label, colour),
        daemon=True,
    )
    t.start()
    return proc


def shutdown(sig=None, frame=None):
    """Gracefully stop all running services."""
    print(f"\n\n{RED}Stopping all AI services ...{RESET}")
    for p in processes:
        if p and p.poll() is None:
            try:
                p.terminate()
            except Exception:
                pass
    time.sleep(1)
    for p in processes:
        if p and p.poll() is None:
            try:
                p.kill()
            except Exception:
                pass
    print(f"{GREEN}All services stopped. Goodbye!{RESET}")
    sys.exit(0)


# Register shutdown for Ctrl+C and system termination
signal.signal(signal.SIGINT,  shutdown)
signal.signal(signal.SIGTERM, shutdown)


if __name__ == "__main__":
    print("=" * 58)
    print("   LIM LIBRARY  -  AI Services Launcher")
    print("=" * 58)
    print(f"  {CYAN}[CHATBOT]{RESET}     AI Chatbot      -> http://localhost:5000")
    print(f"  {YELLOW}[IMG-SEARCH]{RESET}  Image Search    -> http://localhost:5001")
    print("=" * 58)
    print("  Make sure XAMPP (MySQL) and Ollama are running first!")
    print("  Press Ctrl+C to stop both services.")
    print("=" * 58 + "\n")

    # Start chatbot first, then image search (slight stagger avoids log collision)
    p1 = start_service(CHATBOT,   "CHATBOT   ", CYAN)
    processes.append(p1)
    time.sleep(2)

    p2 = start_service(IMGSEARCH, "IMG-SEARCH", YELLOW)
    processes.append(p2)

    # Service definitions for auto-restart loop
    service_defs = [
        (CHATBOT,   "CHATBOT   ", CYAN),
        (IMGSEARCH, "IMG-SEARCH", YELLOW),
    ]

    # Keep running and auto-restart any service that crashes
    try:
        while True:
            time.sleep(3)
            for i, (path, label, colour) in enumerate(service_defs):
                p = processes[i]
                if p is None:
                    continue
                if p.poll() is not None:
                    code = p.returncode
                    print(
                        f"\n{colour}[{label.strip()}]{RESET} "
                        f"exited (code {code}) - restarting in 3 s ...",
                        flush=True,
                    )
                    time.sleep(3)
                    processes[i] = start_service(path, label, colour)
    except KeyboardInterrupt:
        shutdown()
