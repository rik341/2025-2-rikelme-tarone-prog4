document.addEventListener("DOMContentLoaded", async () => {

  // 1) BUSCAR JSON
  const resposta = await fetch("temp_interna.php?formato=json");
  const json = await resposta.json();

  const registros = json.registros_ti;

  // ============================
  // ATUALIZAR MÉDIAS NA TELA
  // ============================
  document.getElementById("valorMedia").textContent =
    json.media_ti ? json.media_ti.toFixed(2) : "--";

  document.getElementById("valorMediaDif").textContent =
    json.diferenca ? json.diferenca.toFixed(2) : "--";

  // Arrays crus
  const labelsBruto = registros.map(item => item.datahora_completa);
  const valoresBruto = registros.map(item => parseFloat(item.ti));

  // ============================
  // REDUZINDO PLOTAGEM
  // ============================
  const step = 20;
  const labels = labelsBruto.filter((_, i) => i % step === 0);
  const valores = valoresBruto.filter((_, i) => i % step === 0);

  // ============================
  // GRÁFICO 1 – REGISTROS
  // ============================
  const ctx1 = document.getElementById("graficoInterna").getContext("2d");

  new Chart(ctx1, {
    type: "line",
    data: {
      labels: labels,
      datasets: [{
        label: "Temperatura Interna (°C)",
        data: valores,
        borderColor: "red",
        borderWidth: 2.5,
        tension: 0.3,
        pointRadius: 0
      }]
    },
    options: {
      responsive: true,
      scales: {
        x: { title: { display: true, text: "Data e Hora" }},
        y: { title: { display: true, text: "Temperatura (°C)" }}
      },
      plugins: {
        decimation: {
          enabled: true,
          algorithm: "min-max",
        }
      }
    }
  });

  // ============================
  // GRÁFICO 2 – MÉDIA DIÁRIA
  // ============================
  const medias = json.media_diaria;

  const labelsMedias = medias.map(item => item.datainclusao);
  const valoresMedias = medias.map(item => item.media_diaria_ti);

  const ctx2 = document.getElementById("graficoMediaDiaria").getContext("2d");

  new Chart(ctx2, {
    type: "bar",
    data: {
      labels: labelsMedias,
      datasets: [{
        label: "Média Diária da Temp. Interna (°C)",
        data: valoresMedias,
        backgroundColor: "orange"
      }]
    },
    options: {
      responsive: true,
      scales: {
        x: { title: { display: true, text: "Data" }},
        y: { title: { display: true, text: "Temperatura (°C)" }}
      }
    }
  });

});
