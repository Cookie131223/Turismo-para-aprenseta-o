// Lista única de destinos do globo 3D.
// Para incluir um destino: acrescente uma linha com nome, país, categoria ("Brasil" ou "Internacional"), latitude, longitude e link.
// link: página do pacote. Use "#" quando ainda não houver pacote — o globo troca sozinho pelo link do pacote
// se existir um destino cadastrado no banco com o mesmo nome (destino.php?id=...).
export const ORIGEM = { nome: 'São Paulo', pais: 'Brasil', lat: -23.55, lon: -46.63 };

export const DESTINOS = [
  // ---------- Brasil ----------
  { nome: 'Rio de Janeiro',      pais: 'Brasil (RJ)', categoria: 'Brasil', lat: -22.91, lon: -43.17, link: '#' },
  { nome: 'Foz do Iguaçu',       pais: 'Brasil (PR)', categoria: 'Brasil', lat: -25.52, lon: -54.59, link: '#' },
  { nome: 'Fernando de Noronha', pais: 'Brasil (PE)', categoria: 'Brasil', lat: -3.85,  lon: -32.42, link: '#' },
  { nome: 'Salvador',            pais: 'Brasil (BA)', categoria: 'Brasil', lat: -12.97, lon: -38.50, link: '#' },
  { nome: 'Gramado',             pais: 'Brasil (RS)', categoria: 'Brasil', lat: -29.38, lon: -50.87, link: '#' },
  { nome: 'Bonito',              pais: 'Brasil (MS)', categoria: 'Brasil', lat: -21.13, lon: -56.48, link: '#' },
  { nome: 'Lençóis Maranhenses', pais: 'Brasil (MA)', categoria: 'Brasil', lat: -2.75,  lon: -42.83, link: '#' },
  { nome: 'Manaus',              pais: 'Brasil (AM)', categoria: 'Brasil', lat: -3.12,  lon: -60.02, link: '#' },
  // ---------- Internacional ----------
  { nome: 'Buenos Aires', pais: 'Argentina',          categoria: 'Internacional', lat: -34.60, lon: -58.38, link: '#' },
  { nome: 'Santiago',     pais: 'Chile',              categoria: 'Internacional', lat: -33.45, lon: -70.67, link: '#' },
  { nome: 'Machu Picchu', pais: 'Peru',               categoria: 'Internacional', lat: -13.16, lon: -72.55, link: '#' },
  { nome: 'Cancún',       pais: 'México',             categoria: 'Internacional', lat: 21.16,  lon: -86.85, link: '#' },
  { nome: 'Orlando',      pais: 'EUA',                categoria: 'Internacional', lat: 28.54,  lon: -81.38, link: '#' },
  { nome: 'Nova York',    pais: 'EUA',                categoria: 'Internacional', lat: 40.71,  lon: -74.01, link: '#' },
  { nome: 'Lisboa',       pais: 'Portugal',           categoria: 'Internacional', lat: 38.72,  lon: -9.14,  link: '#' },
  { nome: 'Paris',        pais: 'França',             categoria: 'Internacional', lat: 48.86,  lon: 2.35,   link: '#' },
  { nome: 'Roma',         pais: 'Itália',             categoria: 'Internacional', lat: 41.90,  lon: 12.50,  link: '#' },
  { nome: 'Cairo',        pais: 'Egito',              categoria: 'Internacional', lat: 30.04,  lon: 31.24,  link: '#' },
  { nome: 'Dubai',        pais: 'Emirados Árabes',    categoria: 'Internacional', lat: 25.20,  lon: 55.27,  link: '#' },
  { nome: 'Tóquio',       pais: 'Japão',              categoria: 'Internacional', lat: 35.68,  lon: 139.69, link: '#' },
];

// Cores das categorias (da paleta do site: --success e --accent em css/main.css)
export const CORES_CATEGORIA = { Brasil: '#38d996', Internacional: '#39d0ff', origem: '#f5f7fb' };
