<?php

namespace App\Helpers;

class States
{
    private static array $us = [
        'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
        'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
        'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho',
        'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas',
        'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
        'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi',
        'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada',
        'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
        'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma',
        'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
        'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah',
        'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia',
        'WI' => 'Wisconsin', 'WY' => 'Wyoming', 'DC' => 'District of Columbia',
    ];

    private static array $ca = [
        'AB' => 'Alberta', 'BC' => 'British Columbia', 'MB' => 'Manitoba',
        'NB' => 'New Brunswick', 'NL' => 'Newfoundland and Labrador', 'NS' => 'Nova Scotia',
        'NT' => 'Northwest Territories', 'NU' => 'Nunavut', 'ON' => 'Ontario',
        'PE' => 'Prince Edward Island', 'QC' => 'Quebec', 'SK' => 'Saskatchewan',
        'YT' => 'Yukon',
    ];

    private static array $gb = [
        'ENG' => 'England', 'SCT' => 'Scotland', 'WLS' => 'Wales', 'NIR' => 'Northern Ireland',
    ];

    private static array $au = [
        'ACT' => 'Australian Capital Territory', 'NSW' => 'New South Wales',
        'NT' => 'Northern Territory', 'QLD' => 'Queensland', 'SA' => 'South Australia',
        'TAS' => 'Tasmania', 'VIC' => 'Victoria', 'WA' => 'Western Australia',
    ];

    private static array $de = [
        'BB' => 'Brandenburg', 'BE' => 'Berlin', 'BW' => 'Baden-Württemberg',
        'BY' => 'Bavaria', 'HB' => 'Bremen', 'HE' => 'Hesse', 'HH' => 'Hamburg',
        'MV' => 'Mecklenburg-Vorpommern', 'NI' => 'Lower Saxony',
        'NW' => 'North Rhine-Westphalia', 'RP' => 'Rhineland-Palatinate',
        'SH' => 'Schleswig-Holstein', 'SL' => 'Saarland', 'SN' => 'Saxony',
        'ST' => 'Saxony-Anhalt', 'TH' => 'Thuringia',
    ];

    private static array $fr = [
        'ARA' => 'Auvergne-Rhône-Alpes', 'BFC' => 'Bourgogne-Franche-Comté',
        'BRE' => 'Brittany', 'CVL' => 'Centre-Val de Loire', 'COR' => 'Corsica',
        'GES' => 'Grand Est', 'HDF' => 'Hauts-de-France', 'IDF' => 'Île-de-France',
        'NOR' => 'Normandy', 'NAQ' => 'Nouvelle-Aquitaine', 'OCC' => 'Occitanie',
        'PDL' => 'Pays de la Loire', 'PAC' => "Provence-Alpes-Côte d'Azur",
    ];

    private static array $es = [
        'AN' => 'Andalusia', 'AR' => 'Aragon', 'AS' => 'Asturias',
        'CB' => 'Cantabria', 'CL' => 'Castile and León', 'CM' => 'Castile-La Mancha',
        'CN' => 'Canary Islands', 'CT' => 'Catalonia', 'EX' => 'Extremadura',
        'GA' => 'Galicia', 'IB' => 'Balearic Islands', 'MD' => 'Madrid',
        'MC' => 'Murcia', 'NC' => 'Navarre', 'PV' => 'Basque Country',
        'RI' => 'La Rioja', 'VC' => 'Valencia',
    ];

    private static array $it = [
        'ABR' => 'Abruzzo', 'BAS' => 'Basilicata', 'CAL' => 'Calabria',
        'CAM' => 'Campania', 'EMR' => 'Emilia-Romagna', 'FVG' => 'Friuli-Venezia Giulia',
        'LAZ' => 'Lazio', 'LIG' => 'Liguria', 'LOM' => 'Lombardy',
        'MAR' => 'Marche', 'MOL' => 'Molise', 'PIE' => 'Piedmont',
        'PUG' => 'Puglia', 'SAR' => 'Sardinia', 'SIC' => 'Sicily',
        'TAA' => 'Trentino-Alto Adige', 'TOS' => 'Tuscany', 'UMB' => 'Umbria',
        'VDA' => "Valle d'Aosta", 'VEN' => 'Veneto',
    ];

    public static function forCountry(string $country): ?array
    {
        return match ($country) {
            'US' => self::$us,
            'CA' => self::$ca,
            'GB' => self::$gb,
            'AU' => self::$au,
            'DE' => self::$de,
            'FR' => self::$fr,
            'ES' => self::$es,
            'IT' => self::$it,
            default => null,
        };
    }
}
