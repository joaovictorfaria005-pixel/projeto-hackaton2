// assets/js/mapa.js
// Mapa interativo com Leaflet e integração com endpoints PHP em mapa.php?action=...

(function(){
  // Coordenadas padrão (São Paulo)
  const defaultLat = -23.5505;
  const defaultLon = -46.6333;

  // Se o PHP injetar coordenadas da cidade do usuário
  const userLat = window.USER_LAT || defaultLat;
  const userLon = window.USER_LON || defaultLon;

  const map = L.map('map').setView([userLat, userLon], 6);

  const tiles = L.tileLayer(
    'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    { attribution: '&copy; OpenStreetMap contributors' }
  );
  tiles.addTo(map);

  const markersLayer = L.layerGroup().addTo(map);

  // Carregar tornados do backend
  function loadTornados() {
    fetch('mapa.php?action=list')
      .then(r => r.json())
      .then(items => {
        markersLayer.clearLayers();
        if (!Array.isArray(items)) return;

        items.forEach(it => {
          const m = L.marker([it.latitude, it.longitude], {
            title: it.titulo
          });
          m.bindPopup(`
            <strong>${it.titulo}</strong><br/>
            Severidade: ${it.severidade}<br/>
            Fonte: ${it.fonte}<br/>
            Observado: ${it.observado_em}
          `);
          markersLayer.addLayer(m);
        });

        // Ajusta o mapa para caber os marcadores
        if (items.length > 0) {
          const latlngs = items.map(i => [i.latitude, i.longitude]);
          const bounds = L.latLngBounds(latlngs);
          map.fitBounds(bounds.pad(0.2));
        } else {
          // Se não houver marcadores, centraliza na cidade do usuário
          map.setView([userLat, userLon], 6);
        }
      })
      .catch(err => {
        console.error(err);
        alert("Erro ao carregar tornados.");
      });
  }

  // Sincronizar alertas com API
  function syncAlerts() {
    fetch('mapa.php?action=sync')
      .then(r => r.json())
      .then(res => {
        alert(`Sincronização concluída. Inseridos: ${res.inserted || 0}`);
        loadTornados();
      })
      .catch(err => {
        console.error(err);
        alert('Erro ao sincronizar.');
      });
  }

  // Drop tabela temporária
  function dropTemp() {
    fetch('mapa.php?action=drop_temp')
      .then(r => r.json())
      .then(res => alert(`Tabela temporária removida: ${res.dropped}`))
      .catch(err => {
        console.error(err);
        alert("Erro ao remover tabela temporária.");
      });
  }

  // Botões
  document.getElementById('btn-refresh').addEventListener('click', loadTornados);
  document.getElementById('btn-sync').addEventListener('click', syncAlerts);
  document.getElementById('btn-drop-temp').addEventListener('click', dropTemp);

  // Inicial
  loadTornados();
})();
