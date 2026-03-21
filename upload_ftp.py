import ftplib
import os
import sys

FTP_HOST = "147.93.99.92"
FTP_USER = "u686584126.thepatilphotography.com"
FTP_PASS = "r0x0N8fY;qWM8l&V"
LOCAL_DIR = "deploy-staging"
REMOTE_DIR = "public_html/stagging"

def upload_dir(ftp, localPath, remotePath):
    print(f"Entering {remotePath}")
    try:
        ftp.cwd(remotePath)
    except Exception as e:
        print(f"Creating directory {remotePath}")
        try:
            ftp.mkd(remotePath)
            ftp.cwd(remotePath)
        except Exception as mkd_err:
            print(f"Failed to switch to {remotePath}: {mkd_err}")
            return

    for item in os.listdir(localPath):
        lPath = os.path.join(localPath, item)
        if os.path.isfile(lPath):
            print(f"Uploading {item} to {remotePath}...")
            # Use chunks rather than single memory load to handle any large file
            try:
                with open(lPath, 'rb') as f:
                    ftp.storbinary(f'STOR {item}', f)
            except Exception as e:
                print(f"Failed to upload {item}: {e}")
        elif os.path.isdir(lPath):
            # Recurse
            upload_dir(ftp, lPath, item)
    
    # After finishing uploading items, return to parent
    ftp.cwd('..')

try:
    print("Connecting to FTP...")
    ftp = ftplib.FTP()
    ftp.connect(FTP_HOST, 21, timeout=30)
    ftp.login(FTP_USER, FTP_PASS)
    print("Connected and logged in.")
    
    upload_dir(ftp, LOCAL_DIR, REMOTE_DIR)
    
    ftp.quit()
    print("Upload completed!")
except Exception as e:
    print(f"Fatal error: {e}")
    sys.exit(1)
