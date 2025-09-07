<?php   
require_once('includes/load.php');
page_require_level(2); 
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Scanner</title>
    <script src="https://unpkg.com/@zxing/library@latest"></script>
    <style>
    body {
        font-family: "Jacques Francois Shadow", serif;
        background: linear-gradient(135deg, #22177A, #1C325B);
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        flex-direction: column;
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
        color: #FCF8F3;
        font-weight: bold;
    }

    /* Container for the camera and result */
    .scanner-container {
        display: flex;
        justify-content: space-between;
        width: 100%;
        height: 80vh;
        /* Adjust to desired height */
        max-width: 1200px;
    }

    .result-container {
        width: 50%;
        padding: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
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
        box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
        position: relative;
    }

    /* Styling for the "Scan Here" text above the camera */
    .scan-message {
        color: white;
        font-size: 24px;
        font-weight: bold;
        background-color: rgba(0, 0, 0, 0.5);
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
    <!-- Logo and Title Outside the Container -->
    <div class="header">
        <img src="/methanoiah/images/methanoiah_logo.png" alt="Methanoiah Academy Logo" class="logo">
        <div class="logo-title">METANOIAH ACADEMY</div>
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
            <!-- "Scan Here" message above the camera -->
            <h1>Scan here!</h1>
            <video id="video"></video>
        </div>
    </div>

    <script>
    const codeReader = new ZXing.BrowserQRCodeReader();
    const videoElement = document.getElementById('video');
    const resultElement = document.getElementById('result');

    let scanning = true; // control flag

    // Function to get the current timestamp
    function getCurrentTimestamp() {
        const now = new Date();
        return now.toLocaleString(); // readable timestamp
    }

    // Function to start scanning
    function startScanning() {
        codeReader.decodeFromVideoDevice(null, videoElement, (result, error) => {
            if (result && scanning) {
                scanning = false; // prevent duplicate scans

                const timestamp = getCurrentTimestamp();
                resultElement.innerHTML =
                    `<strong></strong> ${result.text} <br> <strong>Timestamp:</strong> ${timestamp}`;

                // Show "loading" effect
                resultElement.innerHTML += `<br><span style="color:blue;">Processing...</span>`;

                // Send the result to the server
                fetch('process_qr_code.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `data=${encodeURIComponent(result.text)}&timestamp=${encodeURIComponent(timestamp)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Response Data:', data); // Optional debug
                        // You could display a success or status message here if needed
                    })
                    .finally(() => {
                        // Allow scanning again after a delay
                        setTimeout(() => {
                            scanning = true;
                            resultElement.innerHTML +=
                                `<br><span style="color:green;">Ready for next scan.</span>`;
                        }, 3000); // 3 seconds delay
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