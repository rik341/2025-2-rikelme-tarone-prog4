document.addEventListener("DOMContentLoaded", () => {

  const form = document.getElementById("formPeriodo");

  const spanMedia = document.getElementById("valorMedia");
  const spanDif = document.getElementById("valorMediaDif");

  const canvasRegistros = document.getElementById("graficoInterna");
  const canvasMediaDiaria = document.getElementById("graficoMediaDiaria");

  let chartRegistros = null;
  let chartMedia = null;

  const padraoInicio = "2025-06-01";
  const padraoFim = "2025-06-07";

  document.getElementById("inicio").value = padraoInicio;
  document.getElementById("fim").value = padraoFim;


  async function carregar(inicio, fim) {

    const resp = await fetch(`temp_interna.php?formato=json&inicio=${inicio}&fim=${fim}`);
    const json = await resp.json();

    const registros = json.registros_ti || [];
    const mediaTi = json.media_ti || null;
    const mediaDiaria = json.media_diaria || [];
    const diferenca = json.diferenca || null;

    spanMedia.textContent = mediaTi ? Number(mediaTi).toFixed(2) : "--";
    spanDif.textContent   = diferenca ? Number(diferenca).toFixed(2) : "--";

    const intervalo = parseInt(document.getElementById("intervalo").value) || 20;


    /* ----------------------------------------
       Gráfico 1 — TI Completa
    ---------------------------------------- */
    if (chartRegistros) chartRegistros.destroy();

    if (registros.length > 0) {

      const labelsBruto = registros.map(r => r.datahora_completa);
      const valoresBruto = registros.map(r => parseFloat(r.ti));

      const labels = labelsBruto.filter((_, i) => i % intervalo === 0);
      const valores = valoresBruto.filter((_, i) => i % intervalo === 0);

      chartRegistros = new Chart(canvasRegistros.getContext("2d"), {
        type: "line",
        data: {
          labels,
          datasets: [{
            label: "Temperatura Interna (°C)",
            data: valores,
            borderColor: "red",
            borderWidth: 2.5,
            tension: 0.35,
            pointRadius: 0
          }]
        }
      });
    }


    /* ----------------------------------------
       Gráfico 2 — Média Diária
    ---------------------------------------- */
    if (chartMedia) chartMedia.destroy();

    if (mediaDiaria.length > 0) {

      const labelsMed = mediaDiaria.map(d => d.datainclusao);
      const valoresMed = mediaDiaria.map(d => parseFloat(d.media_diaria_ti));

      chartMedia = new Chart(canvasMediaDiaria.getContext("2d"), {
        type: "bar",
        data: {
          labels: labelsMed,
          datasets: [{
            label: "Média Diária (°C)",
            data: valoresMed,
            backgroundColor: "orange"
          }]
        }
      });
    }

  }


  carregar(padraoInicio, padraoFim);

  form.addEventListener("submit", e => {
    e.preventDefault();
    carregar(
      document.getElementById("inicio").value,
      document.getElementById("fim").value
    );
  });

});
