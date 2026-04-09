    <?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: home.php");
        exit;
    }

    if ($_SESSION['role'] != 'operator' && $_SESSION['role'] != 'all' && $_SESSION['role'] != 'machining') {
        die("Akses ditolak");
    }

    $isAdmin = ($_SESSION['role'] === 'all');
    $isMachining = ($_SESSION['role'] === 'machining');
    $isOperator = ($_SESSION['role'] === 'operator');
    
    if ($isAdmin) {
        $isMachining = true;
        $isOperator = true;
    }


    require "api/config.php";
    $nik = $_SESSION['nik'];

    $stmt = $conn->prepare("SELECT nama FROM karyawan WHERE nik = ?");
    $stmt->bind_param("s", $nik);
    $stmt->execute();
    $result = $stmt->get_result();
    $dataUser = $result->fetch_assoc();

    $nama = $dataUser['nama'] ?? 'Unknown';
    ?>

    <!DOCTYPE html>
    <html>

    <head>
        <title>Transaction</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

        <style>
            :root {
                --bg: #f4f4f4;
                --card: #ffffff;
                --border: #e5e7eb;
                --black: #000;
                --text: #111;
            }

            body {
                margin: 0;
                font-family: 'Montserrat', sans-serif;
                background: var(--bg);
                color: var(--text);
            }

            /* HEADER */
            .header {
                padding: 18px 30px;
                background: #ffffff;
                border-bottom: 1px solid var(--border);
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .user-info strong {
                font-size: 15px;
            }

            .user-info span {
                font-size: 13px;
                color: #666;
            }

            .logout-btn {
                background: black;
                color: white;
                padding: 10px 18px;
                border-radius: 6px;
                text-decoration: none;
                font-size: 13px;
                font-weight: 600;
                transition: 0.2s;
            }

            .logout-btn:hover {
                background: #222;
            }

            /* CONTAINER */
            .container {
                padding: 30px;
                max-width: 1100px;
                margin: auto;
            }

            /* CARD */
            .card {
                background: var(--card);
                padding: 25px;
                border-radius: 12px;
                margin-bottom: 25px;
                box-shadow: 0 15px 40px rgba(0, 0, 0, 0.06);
            }

            /* MODE BUTTON */
            .mode-container {
                display: flex;
                gap: 15px;
            }

            .mode-btn {
                flex: 1;
                padding: 14px;
                font-weight: 600;
                border-radius: 8px;
                border: 1.5px solid black;
                background: white;
                cursor: pointer;
                transition: 0.2s;
            }

            .mode-btn.active {
                background: black;
                color: white;
            }

            /* INPUT */
            input {
                width: 100%;
                padding: 16px;
                font-size: 15px;
                border-radius: 8px;
                border: 1.5px solid black;
                margin-bottom: 15px;
                outline: none;
            }

            input:focus {
                background: black;
                color: white;
            }

            /* BUTTON */
            button.scan-btn {
                width: 100%;
                padding: 15px;
                background: black;
                color: white;
                border: none;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                margin-bottom: 15px;
            }

            button.scan-btn:hover {
                background: #222;
            }

            /* CAMERA */
            .camera-modal {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.7);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 999;
                opacity: 0;
                pointer-events: none;
                transition: 0.3s;
            }

            .camera-modal.active {
                opacity: 1;
                pointer-events: auto;
            }

            .camera-box {
                background: white;
                padding: 15px;
                border-radius: 12px;
                width: 95%;
                max-width: 600px;
            }

            #reader {
                width: 100%;
                /* max-height: 200px;*/
                overflow: hidden; 
            }

            .close-btn {
                margin-top: 15px;
                padding: 12px;
                width: 100%;
                background: black;
                border: none;
                border-radius: 8px;
                color: white;
                font-weight: 600;
            }

            /* HISTORY */
            .history-box {
                max-height: 250px;
                overflow: auto;
                font-size: 14px;
            }

            .history-item {
                padding: 10px 0;
                border-bottom: 1px solid var(--border);
            }

            :root {
                --bg: #f4f4f4;
                --card: #ffffff;
                --border: #e5e7eb;
                --black: #000;
                --text: #111;
            }

            * {
                box-sizing: border-box;
            }

            .back-btn {
                background: #2563eb;
                color: white;
                padding: 10px 18px;
                border-radius: 6px;
                text-decoration: none;
                font-size: 13px;
                font-weight: 600;
                transition: 0.2s;
            }

            .back-btn:hover {
                background: #1e40af;
            }
            .qty-presets {
                display: flex;
                gap: 8px;
                margin-bottom: 15px;
                flex-wrap: wrap;
            }

            .qty-preset-btn {
                flex: 1;
                padding: 10px;
                font-weight: 600;
                border-radius: 8px;
                border: 1.5px solid black;
                background: white;
                cursor: pointer;
                font-size: 14px;
                transition: 0.2s;
                min-width: 50px;
            }

            .qty-preset-btn:hover {
                background: black;
                color: white;
            }
        </style>
    </head>

    <body>

        <div class="header">

            <div class="user-info">
                <strong><?= strtoupper(htmlspecialchars($nama)) ?></strong><br>
                <span>
                    NIK: <?= htmlspecialchars($_SESSION['nik']) ?> |
                    <?= strtoupper($_SESSION['role']) ?>
                </span>
            </div>

            <div style="display:flex; gap:10px; align-items:center;">

                <?php if ($isAdmin): ?>
                    <a href="home_menu.php" class="back-btn">BACK</a>
                <?php endif; ?>

                <a href="signout.php" class="logout-btn">LOGOUT</a>

            </div>

        </div>

        <div class="container">

            <div class="card">
                <div class="mode-container">
                    <?php if ($isMachining): ?>
                        <button class="mode-btn" onclick="setMode('in', this)">IN</button>
                    <?php endif; ?>
                    <?php if ($isOperator): ?>
                        <button class="mode-btn" onclick="setMode('out', this)">OUT</button>
                        <button class="mode-btn" onclick="setMode('return', this)">RETURN</button>
                    <?php endif; ?>
                </div>
            </div>

          <button class="scan-btn" onclick="openCamera()">Scan Barcode</button>
            <input type="text" id="scanInput" placeholder="Scan / Ketik Manual">
            <input type="number" id="qtyInput" placeholder="Input Qty" style="display:none;">
            <div class="qty-presets" id="qtyPresets" style="display:none;">
                <button class="qty-preset-btn" onclick="setQty(1)">1</button>
                <button class="qty-preset-btn" onclick="setQty(5)">5</button>
                <button class="qty-preset-btn" onclick="setQty(10)">10</button>
                <button class="qty-preset-btn" onclick="setQty(15)">15</button>
                <button class="qty-preset-btn" onclick="setQty(20)">20</button>
                <button class="qty-preset-btn" onclick="setQty(25)">25</button>
            </div>

            <div class="card">
                <h3>Last Transactions</h3>
                <div id="historyBox" class="history-box"></div>
            </div>

           <?php if ($isMachining || $isAdmin): ?>
                <div class="card">
                    <h3 style="margin-bottom:20px;">📥 Download History Transaction</h3>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">

                        <!-- Harian -->
                        <div style="
                            border: 1.5px solid #e5e7eb;
                            border-radius: 10px;
                            padding: 18px;
                            display: flex;
                            flex-direction: column;
                            gap: 10px;
                        ">
                            <div style="font-weight:600; font-size:13px; color:#666; text-transform:uppercase; letter-spacing:1px;">
                                Harian
                            </div>
                            <input type="date" id="dlDate" style="margin-bottom:0;">
                            <button class="scan-btn" style="margin-bottom:0;" onclick="downloadHistory()">
                                📥 Download CSV
                            </button>
                        </div>

                        <!-- Bulanan -->
                        <div style="
                            border: 1.5px solid #e5e7eb;
                            border-radius: 10px;
                            padding: 18px;
                            display: flex;
                            flex-direction: column;
                            gap: 10px;
                        ">
                            <div style="font-weight:600; font-size:13px; color:#666; text-transform:uppercase; letter-spacing:1px;">
                                Bulanan
                            </div>
                            <input type="month" id="dlMonth" style="margin-bottom:0;">
                            <button class="scan-btn" style="margin-bottom:0;" onclick="downloadHistoryMonthly()">
                                📥 Download CSV
                            </button>
                        </div>

                    </div>
                </div>
                <?php endif; ?>


        <div id="cameraModal" class="camera-modal">
            <div class="camera-box">
                <div id="reader"></div>
                <button class="close-btn" onclick="closeCamera()">Close Camera</button>
            </div>
        </div>

        <script>
            let mode = "";
            let selectedItem = null;
            let html5QrCode = null;

            const scanInput = document.getElementById("scanInput");
            const qtyInput = document.getElementById("qtyInput");
            const cameraModal = document.getElementById("cameraModal");

            /* MODE */
            function setMode(m, btn) {
                mode = m;

                document.querySelectorAll(".mode-btn").forEach(b => {
                    b.classList.remove("active");
                });

                btn.classList.add("active");
            }

            /* CAMERA */
            function openCamera() {
                cameraModal.classList.add("active");

                if (!html5QrCode) {
                    html5QrCode = new Html5Qrcode("reader");
                }

                html5QrCode.start({
                    facingMode: "environment"
                }, {
                    fps: 100,
                    qrbox: { width: 350, height: 100 }
                },
                    (decodedText) => {
                        scanInput.value = decodedText;
                        closeCamera();
                        scanInput.dispatchEvent(new KeyboardEvent("keydown", {
                            key: "Enter"
                        }));
                    }
                ).catch(err => {
                    alert("Camera Error: " + err);
                });
            }

            function closeCamera() {
                if (html5QrCode) {
                    html5QrCode.stop().then(() => {
                        html5QrCode.clear();
                        cameraModal.classList.remove("active");
                    });
                }
            }

            /* LOAD HISTORY VIA AJAX */
            function loadHistory() {
                fetch("api/get_history.php")
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById("historyBox").innerHTML = html;
                    });
            }

            // AUTO REFRESH HISTORY SETIAP 5 DETIK
            setInterval(loadHistory, 3000);
            loadHistory();

            /* SCAN INPUT */
            scanInput.addEventListener("keydown", function(e) {
                if (e.key !== "Enter") return;

                const val = scanInput.value.trim();
                if (val === "") return;

                if (mode === "") {
                    alert("Pilih mode dulu");
                    return;
                }

                fetch("api/get_item_info.php", {
                        method: "POST",
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            part: val
                        })
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (!res.success) {
                            alert(res.error);
                            return;
                        }

                        selectedItem = res;
                       alert(
                            "MODEL : " + (res.models ?? '-') +
                            "\nPART  : " + res.part +
                            "\nSTOCK : " + res.stock
                        );
                        qtyInput.style.display = "block";
                        document.getElementById("qtyPresets").style.display = "flex"; // ← tambah ini
                        qtyInput.focus();
                    });
            });

            qtyInput.addEventListener("keydown", function(e) {
                if (e.key !== "Enter") return;

                const qtyVal = parseInt(qtyInput.value);
                if (!qtyVal || qtyVal <= 0) {
                    alert("Qty tidak valid");
                    return;
                }

                fetch("api/process_transaction.php", {
                        method: "POST",
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            mode: mode,
                            part: selectedItem.part,
                            qty: qtyVal
                        })
                    })
                    .then(res => res.text())
                    .then(text => {
                        console.log("RAW RESPONSE:", text);

                        try {
                            const res = JSON.parse(text);
                            if (res.success) {
                                alert("Transaction Success");
                                qtyInput.value = "";
                                qtyInput.style.display = "none";
                                document.getElementById("qtyPresets").style.display = "none";
                                selectedItem = null;
                                scanInput.value = "";
                                loadHistory();
                            } else {
                                alert(res.error);
                            }
                        } catch (e) {
                            alert("Server Error. Cek console.");
                        }
                    });
            });

            // Set default tanggal hari ini
                document.addEventListener("DOMContentLoaded", function () {
                    const today = new Date().toISOString().split("T")[0];
                    document.getElementById("dlDate").value = today;
                    const now = new Date();
                    document.getElementById("dlMonth").value =
                        now.getFullYear() + "-" + String(now.getMonth() + 1).padStart(2, "0");
                });

                function downloadHistory() {
                    const date = document.getElementById("dlDate").value;
                    if (!date) { alert("Pilih tanggal dulu"); return; }
                    window.open("api/export_transaction_daily.php?date=" + date, "_blank");
                }

                function downloadHistoryMonthly() {
                    const month = document.getElementById("dlMonth").value;
                    if (!month) { alert("Pilih bulan dulu"); return; }
                    window.open("api/export_transaction_monthly.php?month=" + month, "_blank");
                }

                function setQty(val) {
                    qtyInput.value = val;
                    qtyInput.focus();
                }
        </script>

    </body>

    </html>