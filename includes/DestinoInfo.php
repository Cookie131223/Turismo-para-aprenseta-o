<?php
// Informações extras de cada destino (coordenadas, moeda, fuso, idioma, melhor época).
// Para um destino novo, acrescente uma linha aqui (o nome é comparado sem acentos e sem maiúsculas).
class DestinoInfo {
    const ORIGEM = ['nome' => 'São Paulo', 'iata' => 'GRU', 'lat' => -23.55, 'lon' => -46.63];

    private const DADOS = [
        'toquio'         => ['iata' => 'HND', 'lat' => 35.68,  'lon' => 139.69, 'moeda' => 'JPY', 'simbolo' => '¥',   'fuso' => 'Asia/Tokyo',                    'idioma' => 'Japonês',   'epoca' => 'mar–mai e out–nov'],
        'nova york'      => ['iata' => 'JFK', 'lat' => 40.71,  'lon' => -74.01, 'moeda' => 'USD', 'simbolo' => 'US$', 'fuso' => 'America/New_York',              'idioma' => 'Inglês',    'epoca' => 'abr–jun e set–nov'],
        'roma'           => ['iata' => 'FCO', 'lat' => 41.90,  'lon' => 12.50,  'moeda' => 'EUR', 'simbolo' => '€',   'fuso' => 'Europe/Rome',                   'idioma' => 'Italiano',  'epoca' => 'abr–jun e set–out'],
        'lisboa'         => ['iata' => 'LIS', 'lat' => 38.72,  'lon' => -9.14,  'moeda' => 'EUR', 'simbolo' => '€',   'fuso' => 'Europe/Lisbon',                 'idioma' => 'Português', 'epoca' => 'mar–jun e set–out'],
        'buenos aires'   => ['iata' => 'EZE', 'lat' => -34.60, 'lon' => -58.38, 'moeda' => 'ARS', 'simbolo' => 'AR$', 'fuso' => 'America/Argentina/Buenos_Aires', 'idioma' => 'Espanhol',  'epoca' => 'mar–mai e set–nov'],
        'cancun'         => ['iata' => 'CUN', 'lat' => 21.16,  'lon' => -86.85, 'moeda' => 'MXN', 'simbolo' => 'MX$', 'fuso' => 'America/Cancun',                'idioma' => 'Espanhol',  'epoca' => 'dez–abr'],
        'dubai'          => ['iata' => 'DXB', 'lat' => 25.20,  'lon' => 55.27,  'moeda' => 'AED', 'simbolo' => 'AED', 'fuso' => 'Asia/Dubai',                    'idioma' => 'Árabe',     'epoca' => 'nov–mar'],
        'paris'          => ['iata' => 'CDG', 'lat' => 48.86,  'lon' => 2.35,   'moeda' => 'EUR', 'simbolo' => '€',   'fuso' => 'Europe/Paris',                  'idioma' => 'Francês',   'epoca' => 'abr–jun e set–out'],
        'rio de janeiro' => ['iata' => 'GIG', 'lat' => -22.91, 'lon' => -43.17, 'moeda' => 'BRL', 'simbolo' => 'R$',  'fuso' => 'America/Sao_Paulo',             'idioma' => 'Português', 'epoca' => 'mai–out'],
    ];

    public static function chave(string $nome): string {
        $s = mb_strtolower(trim($nome), 'UTF-8');
        $s = strtr($s, ['á'=>'a','à'=>'a','â'=>'a','ã'=>'a','é'=>'e','ê'=>'e','í'=>'i','ó'=>'o','ô'=>'o','õ'=>'o','ú'=>'u','ü'=>'u','ç'=>'c','ñ'=>'n']);
        return preg_replace('/\s+/', ' ', $s);
    }

    // Junta os dados fixos com o que estiver no banco (lat/lon do banco têm prioridade).
    public static function de(array $destino): array {
        $info = self::DADOS[self::chave($destino['nome'] ?? '')] ?? [];
        if (isset($destino['lat'], $destino['lon']) && $destino['lat'] !== null && $destino['lon'] !== null && $destino['lat'] !== '') {
            $info['lat'] = (float)$destino['lat']; $info['lon'] = (float)$destino['lon'];
        }
        $info += ['iata' => null, 'lat' => null, 'lon' => null, 'moeda' => null, 'simbolo' => null, 'fuso' => null, 'idioma' => null, 'epoca' => null];
        if ($info['lat'] !== null) {
            $info['km'] = (int)round(self::distanciaKm(self::ORIGEM['lat'], self::ORIGEM['lon'], $info['lat'], $info['lon']));
            $info['voo_horas'] = round($info['km'] / 820 + 0.6, 1); // velocidade média de cruzeiro + subida/descida
        } else { $info['km'] = null; $info['voo_horas'] = null; }
        return $info;
    }

    public static function distanciaKm(float $lat1, float $lon1, float $lat2, float $lon2): float {
        $r = 6371; $dLat = deg2rad($lat2 - $lat1); $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return 2 * $r * asin(min(1, sqrt($a)));
    }

    public static function vooTexto(?float $horas): string {
        if ($horas === null) return '—';
        $h = (int)floor($horas); $m = (int)round(($horas - $h) * 60);
        return $h . 'h' . ($m ? str_pad((string)$m, 2, '0', STR_PAD_LEFT) : '');
    }
}
