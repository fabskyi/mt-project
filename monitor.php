<!DOCTYPE html>
<html>

<head>

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --bg: #d1d5db;
            --card: #ffffff;
            --border: #e5e7eb;
            --text: #111;
            --muted: #6b7280;
        }

        body {
            margin: 0;
            font-family: 'Montserrat', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
            zoom: 1.25;
        }

        .topbar {
            background: #ffffff;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border);
        }

        .title {
            font-size: 18px;
            font-weight: 600;
        }

        .clock {
            font-size: 15px;
            color: var(--muted);
        }

        .summary {
            display: flex;
            gap: 25px;
            padding: 25px 40px;
        }

        .summary-box {
            flex: 1;
            background: var(--card);
            border-radius: 15px;
            padding: 12px;
            text-align: center;
            font-weight: 600;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .safe-box {
            color: #16a34a;
        }

        .warn-box {
            color: #d97706;
        }

        .low-box {
            color: #dc2626;
        }

        .model-container {
            flex: 1;
            overflow-y: auto;
            padding: 0 40px 40px 40px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 15px;
        }

        .model-box {
            background: var(--card);
            border-radius: 20px;
            padding: 22px;
            display: flex;
            flex-direction: column;
            height: 280px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .model-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .chart-wrapper {
            position: relative;
            width: 100%;
            height: 250px;
        }

        .footer {
            text-align: center;
            padding: 12px;
            font-size: 12px;
            background: #ffffff;
            color: var(--muted);
            border-top: 1px solid var(--border);
        }

        #scrollToggle {
            padding: 6px 14px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            background: #111;
            color: #fff;
        }

        .dropdown {
            position: relative;
        }

        .dropdown-btn {
            padding: 6px 14px;
            border-radius: 20px;
            border: 1px solid var(--border);
            background: #fff;
            cursor: pointer;
            font-weight: 600;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: 40px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px;
            max-height: 300px;
            overflow-y: auto;
            min-width: 180px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            z-index: 100;
        }

        .dropdown-content label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            cursor: pointer;
        }

        .nav-btn {
            padding: 6px 14px;
            border-radius: 20px;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }

        .back-btn {
            background: #2563eb;
            color: #fff;
        }

        .logout-btn {
            background: #dc2626;
            color: #fff;
        }

        .title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 18px;
            font-weight: 600;
        }

        .logo {
            height: 30px;
        }
    </style>
</head>

<body>

    <div class="topbar">
        <div class="title">
            <img src="assets/yanmar.png" class="logo">
            <span>Supermarket Machine Shop</span>
        </div>
        <div style="display:flex; align-items:center; gap:15px;">

            <!-- DROPDOWN -->
            <div class="dropdown">
                <button class="dropdown-btn" id="dropdownBtn">Filter Model ▾</button>
                <div class="dropdown-content" id="dropdownContent"></div>
            </div>
            <button id="scrollToggle">Pause</button>
            <div class="clock" id="clock"></div>
        </div>
    </div>

    <div class="summary" style="display: none;">
        <div class="summary-box safe-box" id="safeCount">SAFE: 0</div>
        <div class="summary-box low-box" id="lowCount">LOW: 0</div>
    </div>

    <div class="model-container" id="modelContainer"></div>

    <div class="footer">
        PT. Yanmar Diesel Indonesia
    </div>

    <script>
        let charts = {};
        let selectedModels = new Set();
        let dropdownInitialized = false;
        let modelPartOrder = {};
        let savedModels = localStorage.getItem("selectedModels");

        if (savedModels) {
            selectedModels = new Set(JSON.parse(savedModels));
        }

        const dropdownBtn = document.getElementById("dropdownBtn");
        const dropdownContent = document.getElementById("dropdownContent");
        const container = document.getElementById("modelContainer");
        const toggleBtn = document.getElementById("scrollToggle");

        /* ===============================
           DROPDOWN
        ================================ */

        dropdownBtn.addEventListener("click", () => {
            dropdownContent.style.display =
                dropdownContent.style.display === "block" ? "none" : "block";
        });

        document.addEventListener("click", function(e) {
            if (!e.target.closest(".dropdown")) {
                dropdownContent.style.display = "none";
            }
        });

        /* ===============================
           CLOCK
        ================================ */

        function updateClock() {
            const now = new Date();
            document.getElementById("clock").innerHTML =
                now.toLocaleDateString() + " " + now.toLocaleTimeString();
        }
        setInterval(updateClock, 1000);
        updateClock();

        /* ===============================
           LOAD DATA (PRO VERSION)
        ================================ */

        async function loadData() {
            const res = await fetch("api/monitor_api.php?mode=all&_=" + Date.now(), {
                cache: "no-store"
            });
            const response = await res.json();
            if (!response.success) return;
            const data = response.data;


            let safe = 0,
                low = 0;

            const grouped = {};

            // kelompokkan data per model
            data.forEach(item => {
                if (!grouped[item.model_name]) grouped[item.model_name] = [];
                grouped[item.model_name].push(item);
            });

            // hitung SAFE dan LOW
            Object.values(grouped).forEach(modelParts => {
                const stocks = modelParts.map(i => parseInt(i.current_stock));
                const safetyStocks = modelParts.map(i => Math.ceil(parseInt(i.safety_stock) * 1.2));
                const minStock = Math.min(...stocks);

                modelParts.forEach((item, idx) => {
                    const stock = parseInt(item.current_stock);
                    const safetyStock = safetyStocks[idx];

                    if (stock > safetyStock) safe++; // hijau → SAFE
                    else if (stock === minStock) low++; // merah → LOW
                    // kuning / warning bisa ditambahkan jika mau
                });
            });

            // update top bar
            document.getElementById("safeCount").innerHTML = "SAFE: " + safe;
            document.getElementById("lowCount").innerHTML = "LOW: " + low;

            // INIT DROPDOWN SEKALI
            if (!dropdownInitialized) {
                const selectAllLabel = document.createElement("label");
                selectAllLabel.innerHTML = `<input type="checkbox" id="selectAllModels"> Select All`;
                dropdownContent.appendChild(selectAllLabel);

                const unselectAllLabel = document.createElement("label");
                unselectAllLabel.innerHTML = `<input type="checkbox" id="unselectAllModels"> Unselect All`;
                dropdownContent.appendChild(unselectAllLabel);

                dropdownContent.appendChild(document.createElement("hr"));
                Object.keys(grouped).forEach(modelName => {

                    if (selectedModels.size === 0 || selectedModels.has(modelName)) {
                        selectedModels.add(modelName);
                    }

                    const label = document.createElement("label");
                    label.innerHTML = `
                                    <input type="checkbox" value="${modelName}" ${selectedModels.has(modelName) ? "checked" : ""}>
                                    ${modelName}
                                    `;

                    label.querySelector("input").addEventListener("change", function() {

                        if (this.checked) {
                            selectedModels.add(modelName);
                            showModel(modelName);
                        } else {
                            selectedModels.delete(modelName);
                            hideModel(modelName);
                        }

                        localStorage.setItem("selectedModels", JSON.stringify([...selectedModels]));
                    });

                    dropdownContent.appendChild(label);
                });

                dropdownInitialized = true;
                setTimeout(() => {

                    const selectAll = document.getElementById("selectAllModels");
                    const unselectAll = document.getElementById("unselectAllModels");

                    selectAll.addEventListener("change", function() {

                        if (this.checked) {

                            document.querySelectorAll('#dropdownContent input[value]').forEach(cb => {

                                if (cb.value) {

                                    cb.checked = true;
                                    selectedModels.add(cb.value);
                                    showModel(cb.value);

                                }

                            });

                        }

                        localStorage.setItem("selectedModels", JSON.stringify([...selectedModels]));

                    });

                    unselectAll.addEventListener("change", function() {

                        if (this.checked) {

                            document.querySelectorAll('#dropdownContent input[type="checkbox"]').forEach(cb => {

                                if (cb.value) {

                                    cb.checked = false;
                                    selectedModels.delete(cb.value);
                                    hideModel(cb.value);

                                }

                            });

                        }

                        localStorage.setItem("selectedModels", JSON.stringify([...selectedModels]));

                    });

                }, 100);
            }

            updateCharts(grouped);

            document.getElementById("safeCount").innerHTML = "SAFE: " + safe;
            // document.getElementById("warnCount").innerHTML = "WARNING: " + warn;
            document.getElementById("lowCount").innerHTML = "LOW: " + low;
        }

        /* ===============================
           UPDATE CHART TANPA DESTROY
        ================================ */

        function updateCharts(grouped) {
            // CONTROL CHECKBOX

            Object.keys(grouped).forEach(modelName => {

                const chartId = "chart-" + modelName.replace(/\s+/g, '');
                const modelData = grouped[modelName];

                modelData.sort((a, b) => a.part_name.localeCompare(b.part_name));

                if (!modelPartOrder[modelName]) {
                    modelPartOrder[modelName] = modelData.map(i => i.part_name);
                }

                const fixedOrder = modelPartOrder[modelName];

                const labels = fixedOrder;
                const stocks = [];
                const safety = [];
                const colors = [];

                fixedOrder.forEach(partName => {

                    const found = modelData.find(i => i.part_name === partName);

                    if (found) {
                        const stock = parseInt(found.current_stock);
                        const safetyStock = Number(found.safety_stock) * 1.2;
                        const stockValues = modelData.map(i => parseInt(i.current_stock));
                        const minStockValue = Math.min(...stockValues);
                        stocks.push(Number(stock));
                        safety.push(Number(safetyStock));

                        if (stock === minStockValue) {
                            colors.push("#dc2626");
                        } else if (stock < safetyStock) {
                            colors.push("#facc15");
                        } else {
                            colors.push("#16a34a");
                        }

                    } else {
                        stocks.push(0);
                        safety.push(0);
                        colors.push("#ccc");
                    }
                });


                if (!charts[chartId]) {

                    const box = document.createElement("div");
                    box.className = "model-box";
                    box.id = "box-" + chartId;

                    box.innerHTML = `
                                <div class="model-title">${modelName}</div>
                                <div class="chart-wrapper"><canvas id="${chartId}"></canvas></div>`;
                    container.appendChild(box);

                    const ctx = document.getElementById(chartId).getContext("2d");
                    const minStock = Math.min(...stocks);

                    charts[chartId] = new Chart(ctx, {
                        type: "bar",
                        data: {
                            labels: labels,
                            datasets: [{
                                    label: "",
                                    data: stocks,
                                    backgroundColor: colors,
                                    barThickness: 12,
                                    maxBarThickness: 30,
                                    borderWidth: 0
                                },
                                {
                                    type: "line",
                                    label: "Safety Stock",
                                    data: safety,
                                    borderColor: "#2c08f5",
                                    borderWidth: 1.5,
                                    borderDash: [20, 5],
                                    pointRadius: 0,
                                    pointHoverRadius: 0,
                                    pointStyle: 'circle',
                                    fill: false,
                                    tension: 0
                                },
                                {
                                    type: "line",
                                    label: "",
                                    data: Array(labels.length).fill(minStock),
                                    borderColor: "#000",
                                    borderWidth: 2,
                                    borderDash: [20, 10],
                                    pointRadius: 0,
                                    fill: false,
                                    tension: 0,
                                    hidden: true
                                }
                            ]
                        },
                        plugins: [valueLabelPlugin],
                        options: {
                            indexAxis: 'y',
                            animation: false,
                            responsive: true,
                            maintainAspectRatio: false,
                            layout: {
                                padding: {
                                    right: 50
                                }
                            },
                            plugins: {
                                legend: {
                                    display: true,
                                    position: "bottom",
                                    labels: {
                                        generateLabels(chart) {

                                            const datasets = chart.data.datasets;

                                            return datasets
                                                .filter(ds => ds.label)
                                                .map((ds, i) => ({
                                                    text: "● - ● - ● - ● -  " + ds.label, // titik lebih besar
                                                    fillStyle: 'transparent',
                                                    strokeStyle: '#2c08f5',
                                                    fontColor: '#2c08f5',
                                                    lineWidth: 0,
                                                    hidden: false,
                                                    datasetIndex: i
                                                }));
                                        },
                                        font: {
                                            size: 12,
                                            weight: "bold"
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    grace: '15%'
                                },
                                y: {
                                    ticks: {
                                        autoSkip: false
                                    }
                                }
                            }
                        }
                    });

                } else {

                    const chart = charts[chartId];

                    chart.data.labels = labels;
                    chart.data.datasets[0].data = stocks;
                    chart.data.datasets[0].backgroundColor = colors;
                    chart.data.datasets[1].data = [...safety];
                    chart.data.datasets[2].data = Array(labels.length).fill(minStock);
                    chart.data.datasets[1].data.length = 0;
                    chart.data.datasets[1].data.push(...safety);
                    chart.update('active');
                }

                if (selectedModels.has(modelName)) {
                    showModel(modelName);
                }
            });
        }

        function hideModel(modelName) {
            const chartId = "chart-" + modelName.replace(/\s+/g, '');
            const box = document.getElementById("box-" + chartId);
            if (box) box.style.display = "none";
        }

        function showModel(modelName) {
            const chartId = "chart-" + modelName.replace(/\s+/g, '');
            const box = document.getElementById("box-" + chartId);
            if (box) box.style.display = "flex";
        }

        let autoScrollActive = true;
        let scrollDirection = 1;
        let scrollSpeed = 0.5;
        let animationId;
        let isRefreshing = false;

        function autoScroll() {

            if (!autoScrollActive) return;

            container.scrollTop += scrollSpeed * scrollDirection;

            const bottomReached =
                container.scrollTop + container.clientHeight >= container.scrollHeight;

            const topReached =
                container.scrollTop <= 0;

            // ===== SAMPAI BAWAH =====
            if (bottomReached && scrollDirection === 1) {

                scrollDirection = -1;

                if (!isRefreshing) {
                    isRefreshing = true;

                    loadData().then(() => {
                        isRefreshing = false;
                    });
                }
            }

            // ===== SAMPAI ATAS =====
            if (topReached && scrollDirection === -1) {

                scrollDirection = 1;

                if (!isRefreshing) {
                    isRefreshing = true;

                    loadData().then(() => {
                        isRefreshing = false;
                    });
                }
            }

            animationId = requestAnimationFrame(autoScroll);
        }

        function startScroll() {
            cancelAnimationFrame(animationId);
            animationId = requestAnimationFrame(autoScroll);
        }

        function stopScroll() {
            cancelAnimationFrame(animationId);
        }

        startScroll();

        /* TOGGLE BUTTON ONLY */
        toggleBtn.addEventListener("click", () => {
            autoScrollActive = !autoScrollActive;

            if (autoScrollActive) {
                toggleBtn.innerText = "Pause";
                toggleBtn.style.background = "#111";
                startScroll();
            } else {
                toggleBtn.innerText = "Play";
                toggleBtn.style.background = "#16a34a";
                stopScroll();
            }
        });


        const valueLabelPlugin = {
            id: 'valueLabel',
            afterDatasetsDraw(chart) {

                const {
                    ctx
                } = chart;

                chart.data.datasets.forEach((dataset, i) => {

                    if (i !== 0) return;

                    const meta = chart.getDatasetMeta(i);

                    meta.data.forEach((bar, index) => {

                        const value = dataset.data[index];

                        ctx.fillStyle = "#000";
                        ctx.font = "bold 12px sans-serif";
                        ctx.textAlign = "left";
                        ctx.textBaseline = "middle";

                        ctx.fillText(
                            value,
                            Math.min(bar.x + 10, chart.chartArea.right - 40),
                            bar.y
                        );

                    });

                });

            }
        };

        loadData();

        setInterval(function() {
            location.reload(true);
        }, 3600000);
        // setInterval(loadData, 2000);
    </script>

</body>

</html>