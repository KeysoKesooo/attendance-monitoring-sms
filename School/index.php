<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Scanner</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@zxing/library@latest"></script>

    <style>
    body {
        font-family: "Bebas Neue", sans-serif;
        letter-spacing: 1px;
        /* ✅ Small space between letters */
        /* ✅ Apply Roboto Slab */
        background: #690B22;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        flex-direction: column;
    }

    ::-webkit-scrollbar {
        width: 0px;
        height: 0px;
    }

    /* Login Button Positioned at the Top Left */
    .login-btn {
        position: fixed;
        top: 20px;
        right: 10px;
        z-index: 9999;
    }

    .login-btn button {
        background-color: #F8FAFC;
        font-size: 18px;
        letter-spacing: 2px;
        text-transform: uppercase;
        display: inline-block;
        text-align: center;
        font-weight: bold;
        padding: 0.7em 2em;
        border: 3px solid #690B22;
        border-radius: 2px;
        position: relative;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.16), 0 3px 6px rgba(0, 0, 0, 0.1);
        color: #690B22;
        text-decoration: none;
        transition: 0.3s ease all;
    }

    button:hover,
    .login-btn:focus {
        color: white;
    }

    .login-btn:hover:before,
    .login-btn:focus:before {
        transition: 0.5s all ease;
        left: 0;
        right: 0;
        opacity: 1;
    }

    .login-btn:active {
        transform: scale(0.9);
    }

    .login-btn :active {
        transform: scale(0.9);
    }

    /* Styling for the logo and title */
    .header {
        text-align: center;
        margin-bottom: 20px;
    }

    .logo {
        width: 150px;
        height: 150px;
        margin-bottom: 10px;
    }

    .logo-title {
        font-size: 36px;
        color: #F8FAFC;
        font-weight: bold;
    }

    /* Container for the camera and result */
    .scanner-container {
        display: flex;
        justify-content: space-between;
        width: 100%;
        height: 80vh;
        max-width: 1200px;
    }

    .result-container {
        width: 50%;
        padding: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 0 10px #D17D98;
        overflow-y: auto;
        text-align: center;
    }

    .camera-container {
        width: 50%;
        padding: 10px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 0 10px #D17D98;
        position: relative;
    }

    /* Loading overlay styles */
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.7);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        color: white;
        font-size: 24px;
        border-radius: 8px;
        z-index: 10;
        display: none;
        /* Hidden by default */
    }

    .spinner {
        border: 5px solid #f3f3f3;
        border-top: 5px solid #690B22;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
        margin-bottom: 15px;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* Styling for the "Scan Here" text above the camera */
    .scan-message {
        color: white;
        font-size: 24px;
        font-weight: bold;
        background-color: #F8FAFC;
        padding: 10px;
        border-radius: 5px;
        text-align: center;
        margin-bottom: 10px;
    }

    h1 {
        color: #333;
        margin-bottom: 20px;
    }

    #video {
        width: 100%;
        height: 100%;
        border-radius: 8px;
    }

    #result {
        font-size: 18px;
        color: #555;
        white-space: pre-line;
        font-weight: bold;
    }

    /* Media Queries */
    @media (max-width: 768px) {
        .scanner-container {
            flex-direction: column;
            height: auto;
        }

        .camera-container,
        .result-container {
            width: 100%;
            padding: 10px;
        }

        #video {
            width: 100%;
            height: 100%;
        }
    }
    </style>
</head>

<body>
    <!-- Login .login-btn Container -->
    <div class="login-btn">
        <a href="login.php">
            <button>Login</button>
        </a>
    </div>

    <!-- Logo and Title Outside the Container -->
    <div class="header">
        <img src="/TMCSHS/images/school_logo.png" alt="TMCSHS Logo" class="logo">
        <div class="logo-title">TRECE MARTIRES CITY SENIOR HIGH SCHOOL</div>
    </div>

    <!-- QR Scanner and Result Container -->
    <div class="scanner-container">
        <!-- Result Container (Left) -->
        <div class="result-container">
            <h1>Scan Result</h1>
            <p id="result">Scan a QR code to see the result here.</p>
        </div>

        <!-- Camera Container (Right) -->
        <div class="camera-container">
            <!-- Loading overlay -->
            <div class="loading-overlay" id="loadingOverlay">
                <div class="spinner"></div>
                <div>Processing scan...</div>
            </div>

            <!-- "Scan Here" message above the camera -->
            <h1>Scan here!</h1>
            <video id="video"></video>
        </div>
    </div>

    <script>
    const codeReader = new ZXing.BrowserQRCodeReader();
    const videoElement = document.getElementById('video');
    const resultElement = document.getElementById('result');
    const loadingOverlay = document.getElementById('loadingOverlay');

    let isScanningAllowed = true; // Flag to control scanning frequency

    // Function to get the current timestamp
    function getCurrentTimestamp() {
        const now = new Date();
        return now.toLocaleString(); // Formats the timestamp to a readable string
    }

    // Function to show loading overlay
    function showLoading() {
        loadingOverlay.style.display = 'flex';
    }

    // Function to hide loading overlay
    function hideLoading() {
        loadingOverlay.style.display = 'none';
    }

    // Function to start scanning
    function startScanning() {
        codeReader.decodeFromVideoDevice(null, videoElement, (result, error) => {
            if (result && isScanningAllowed) {
                isScanningAllowed = false; // Disable further scanning
                showLoading(); // Show loading overlay

                const timestamp = getCurrentTimestamp();
                resultElement.innerHTML =
                    `<strong>${result.text}</strong>  <br> <strong>Timestamp: ${timestamp}</strong> `;

                // Send the result to the server to check attendance status and update timestamps
                fetch('process_qr_code.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `data=${encodeURIComponent(result.text)}&timestamp=${encodeURIComponent(timestamp)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Response Data:', data); // Log the response data
                        if (data.success) {
                            alert(data.message);
                        } else {
                            alert('Failed to process the attendance.');
                        }

                        // Hide loading overlay after processing
                        hideLoading();

                        // Re-enable scanning after 5 seconds (you can adjust this time)
                        setTimeout(() => {
                            isScanningAllowed = true;
                        }, 1000000); // 5000 milliseconds = 5 seconds
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        hideLoading();
                        isScanningAllowed = true; // Re-enable scanning on error
                    });
            }
            if (error) {
                console.error(error);
            }
        }).catch(error => {
            console.error(error);
        });
    }

    // Start the scanning process
    startScanning();
    </script>
</body>

</html>