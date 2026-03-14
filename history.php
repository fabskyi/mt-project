<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}

$role = $_SESSION['role'];

if ($role == "ms1") {
    $lokasi = 1;
} elseif ($role == "ms2") {
    $lokasi = 2;
} elseif ($role == "all") {
    $lokasi = $_GET['lokasi'] ?? 1;
} else {
    header("Location: home.php");
    exit;
}
?>

<!doctype html>
<html>

<head>

    <meta charset="UTF-8">
    <title>Stock History</title>

    <style>
        body {
            font-family: Segoe UI;
            background: #f1f5f9;
            margin: 0;
        }

        /* HEADER */

        .header {
            background: white;
            padding: 18px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .title {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
        }

        .btn-back {
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            cursor: pointer;
        }

        .btn-back:hover {
            background: #1d4ed8;
        }

        /* CONTAINER */

        .container {
            padding: 30px;
        }

        /* FILTER */

        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            background: white;
        }

        /* TABLE */

        .table-box {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8fafc;
        }

        th {
            padding: 14px;
            font-size: 12px;
            text-transform: uppercase;
            color: #475569;
        }

        td {
            padding: 14px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 14px;
        }

        tbody tr:hover {
            background: #f1f5f9;
        }

        .type-in {
            color: #16a34a;
            font-weight: 600;
        }

        .type-out {
            color: #dc2626;
            font-weight: 600;
        }

        .type-return {
            color: #f59e0b;
            font-weight: 600;
        }
    </style>

</head>

<body>

    <div class="header">

        <div class="title">
            Stock History - MS<?php echo $lokasi; ?>
        </div>

        <button class="btn-back" onclick="goDashboard()">
            ← Back to Dashboard
        </button>

    </div>

    <div class="container">

        <div class="filters">

            <select id="timeFilter" onchange="loadHistory()">
                <option value="today">Today</option>
                <option value="week">Week</option>
                <option value="month">Month</option>
            </select>

            <select id="typeFilter" onchange="loadHistory()">
                <option value="">ALL</option>
                <option value="IN">IN</option>
                <option value="OUT">OUT</option>
            </select>

        </div>

        <div class="table-box">

            <table>

                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Part Name</th>
                        <th>Barcode</th>
                        <th>Type</th>
                        <th>Qty</th>
                        <th>Before</th>
                        <th>After</th>
                        <th>Note</th>
                    </tr>
                </thead>

                <tbody id="historyBody"></tbody>

            </table>

        </div>

    </div>

    <script>
        let lokasi = <?php echo $lokasi; ?>;

        function loadHistory() {

            let time = document.getElementById("timeFilter").value;
            let type = document.getElementById("typeFilter").value;

            fetch(`./api/get_history_page.php?lokasi=${lokasi}&time=${time}&type=${type}`)

                .then(res => res.json())

                .then(res => {

                    let body = document.getElementById("historyBody");

                    body.innerHTML = "";

                    res.data.forEach(row => {

                        let dateTime = row.created_at.split(" ");
                        let date = dateTime[0];
                        let time = dateTime[1];

                        let t = row.type.toLowerCase();

                        if (t == "in") typeClass = "type-in";
                        if (t == "out") typeClass = "type-out";


                        body.innerHTML += `
                            <tr>
                                <td>${date}</td>
                                <td>${time}</td>
                                <td>${row.part_name}</td>
                                <td>${row.part_number}</td>
                                <td class="${typeClass}">
                                ${row.type.toUpperCase()}
                                </td>
                                <td>${row.qty}</td>
                                <td>${row.before_stock}</td>
                                <td>${row.after_stock}</td>
                                <td>${row.note ?? '-'}</td>
                            </tr>
                            `;
                    });
                });
        }

        function goDashboard() {
            window.location.href = "index.php?lokasi=" + lokasi;
        }

        loadHistory();
    </script>

</body>

</html>