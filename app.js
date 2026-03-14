/* ================= GLOBAL ================= */
let scannerEnabled = true;
let mode = "in";
let lastScan = "";
let lastScanTime = 0;
let currentLokasi = 1;
let isLoading = false;
let refreshInterval = null;

/* ================= SET LOKASI ================= */
function setLokasi(id) {
  if (currentLokasi === id) return;

  currentLokasi = id;

  document.getElementById("btnMS1")?.classList.toggle("active", id === 1);
  document.getElementById("btnMS2")?.classList.toggle("active", id === 2);

  const title = document.getElementById("pageTitle");
  if (title) {
    title.innerText =
      id === 1
        ? "SUPERMARKET MACHINE SHOP - MS1"
        : "SUPERMARKET MACHINE SHOP - MS2";
  }

  loadItems(true);
}

/* ================= LOAD ITEMS ================= */
async function loadItems(force = false) {
  if (isLoading && !force) return;

  try {
    isLoading = true;

    const res = await fetch("./api/get_items.php?lokasi_id=" + currentLokasi);
    const response = await res.json();

    if (!response.success) {
      console.error("Response error:", response);
      return;
    }

    const data = response.data;

    const tbody = document.querySelector("#stockTable tbody");
    let rows = "";

    let totalItem = 0;
    let totalStock = 0;
    let lowStock = 0;

    data.forEach((item, index) => {
      const stock = parseInt(item.current_stock) || 0;
      const safety = parseInt(item.safety_stock) || 0;

      totalItem++;
      totalStock += stock;

      let status = "";
      if (stock <= safety) {
        status = '<span class="low">LOW</span>';
        lowStock++;
      } else {
        status = '<span class="ok">OK</span>';
      }

      rows += `
        <tr>
          <td>${index + 1}</td>
          <td>${item.model_name}</td>
          <td>${item.part_name}</td>
          <td>${item.part_number}</td>
          <td>${stock}</td>
          <td>${safety}</td>
          <td>${status}</td>
        </tr>
      `;
    });

    tbody.innerHTML = rows;

    document.getElementById("totalItem").innerText = totalItem;
    document.getElementById("totalStock").innerText = totalStock;
    document.getElementById("lowStock").innerText = lowStock;
  } catch (err) {
    console.error("LOAD ERROR:", err);
  } finally {
    isLoading = false;
  }
}

/* ================= PROCESS SCAN ================= */
async function processScan(code) {
  if (!code || isLoading) return;

  const qtyInput = document.getElementById("scanQty");
  const qty = parseInt(qtyInput?.value) || 1;

  if (qty <= 0) {
    showToast("Qty tidak valid", "warning");
    return;
  }

  const now = Date.now();
  if (code === lastScan && now - lastScanTime < 300) return;

  lastScan = code;
  lastScanTime = now;

  const form = new FormData();
  form.append("part_number", code.trim());
  form.append("type", mode);
  form.append("qty", qty);
  form.append("lokasi_id", currentLokasi);

  try {
    isLoading = true;

    const res = await fetch("./api/scan.php", {
      method: "POST",
      body: form,
    });

    const result = await res.json();

    if (result.status === "OK") {
      await loadItems(true);

      const arrow = mode === "in" ? "+" : "-";
      showToast(
        `${result.model} ${mode.toUpperCase()} (${arrow}${qty}) | Stock: ${result.stock}`,
        mode,
      );
    } else {
      showToast(result.msg || "Part tidak ditemukan", "warning");
    }
  } catch (err) {
    console.error("SCAN ERROR:", err);
    showToast("Server error", "warning");
  } finally {
    isLoading = false;
  }
}

/* ================= MODE ================= */
function setMode(m) {
  mode = m;
  document.getElementById("btnIn")?.classList.toggle("active", m === "in");
  document.getElementById("btnOut")?.classList.toggle("active", m === "out");
}

/* ================= ADD ITEM ================= */
async function saveItem() {
  const model = document.getElementById("add_model").value.trim();
  const part = document.getElementById("add_part_name").value.trim();
  const partNumber = document.getElementById("add_part_number").value.trim();

  if (!model || !part || !partNumber) {
    showToast("Semua field wajib diisi", "warning");
    return;
  }

  const form = new FormData();
  form.append("model", model);
  form.append("part", part);
  form.append("part_number", partNumber);
  form.append("current_stock", document.getElementById("add_stock").value || 0);
  form.append(
    "safety_stock",
    document.getElementById("add_safety")?.value || 0,
  );
  form.append("lokasi_id", currentLokasi);

  try {
    const res = await fetch("./api/add_item.php", {
      method: "POST",
      body: form,
    });

    const data = await res.json();

    if (data.status === "OK") {
      closeAddModal();
      showToast("Item berhasil ditambahkan", "in");
      loadItems(true);
    } else {
      showToast(data.msg || "Gagal menambahkan", "warning");
    }
  } catch (err) {
    console.error("ADD ERROR:", err);
    showToast("Server error", "warning");
  }
}

/* ================= INIT ================= */
document.addEventListener("DOMContentLoaded", () => {
  loadItems(true);
  setMode("in");

  const scannerInput = document.getElementById("scannerInput");
  if (scannerInput) {
    scannerInput.focus();
    document.addEventListener("click", () => scannerInput.focus());
  }

  // Auto refresh (lebih aman)
  refreshInterval = setInterval(() => {
    if (scannerEnabled && !isLoading) {
      loadItems();
    }
  }, 5000);
});

/* ================= MODAL CONTROL ================= */
function openAddModal() {
  document.getElementById("addModal").style.display = "flex";
}

function closeAddModal() {
  document.getElementById("addModal").style.display = "none";
}
