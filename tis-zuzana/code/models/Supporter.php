<?php

use Nette\Utils\Random;

class Supporter extends DataObject
{
    private static $db = array(
        'Email' => 'Varchar(100)',
        'Name' => 'Varchar(255)',
        'City' => 'Varchar(100)',
        'Country' => 'Varchar(100)',
        'Show' => 'Boolean',

        'ConfirmationHash' => 'Varchar(100)',
        'ConfirmedEmail' => 'Varchar(100)'
    );

    private static $indexes = array(
        'Email' => true,
        'ConfirmedEmail' => true,
        'ConfirmationHash' => 'unique("ConfirmationHash")'
    );

    public function sendConfirmationEmail()
    {
        $email = new PHPMailer();

        $email->isSMTP();

        //$email->SMTPDebug = 3;

        $email->CharSet = 'utf-8';
        $email->Host = SMTP_HOST;
        $email->SMTPAuth = true;
        $email->Username = SMTP_LOGIN;
        $email->Password = SMTP_PASSWORD;
        $email->SMTPSecure = SMTP_PROTOCOL;
        $email->Port = SMTP_PORT;

        $email->From = 'info@odpovedztezuzane.sk';
        $email->FromName = 'Odpovedzte Zuzane';

        $email->addAddress($this->Email);

        $name = empty($this->Name) ? '' : ' ' . $this->Name;

        $email->Subject = 'Odpovedzte Zuzane: Overenie emailovej adresy';
        $email->Body = "Dobrý deň$name,\n\n";
        $email->Body .= "ďakujeme za Vašu podporu. Ak sa chcete zapojiť do výzvy Odpovedzte Zuzane, potvrďte záujem kliknutím na nasledujúci odkaz:\n\n";
        $email->Body .= $this->getConfirmationLink() . "\n\n";
        $email->Body .= "Pomáhate nám tým predísť viacnásobným podpisom a odoslaniam od fiktívnych osôb.\n\n";
        $email->Body .= "Ďakujeme\n\n";
        $email->Body .= "S pozdravom\n";
        $email->Body .= "Transparency International SK";

        $email->send();
    }

    public static function confirmed()
    {
        return static::get()->where('ConfirmedEmail = Email');
    }

    public static function confirmedEmailByHash($hash)
    {
        $supporter = static::get()->filter(array('ConfirmationHash' => $hash))->first();

        if ($supporter instanceof Supporter && $supporter->LastEdited > date('Y-m-d H:i:s', time() - (24 * 3600 * 7)))
        {
            $supporter->ConfirmedEmail = $supporter->Email;
            $supporter->write();

            return true;
        }
        else
        {
            return false;
        }
    }

    protected function getConfirmationLink()
    {
        return MAIL_BASE_URL . 'home/confirm/' . $this->generateConfirmationHash();
    }

    protected function generateConfirmationHash()
    {
        $hash = Random::generate(10);

        try
        {
            $this->ConfirmationHash = $hash;
            $this->write();

            return $this->ConfirmationHash;
        }
        catch (SS_DatabaseException $e)
        {
            if (strpos($e->getMessage(), 'Duplicate entry'))
            {
                echo $this->generateConfirmationHash();
            }
            else
            {
                throw $e;
            }
        }
    }

    public function getCountryName()
    {
        $countries = array(
            'sk' => array("AF" =>"Afganistan","AX" =>"Alandy","AL" =>"Albánsko","DZ" =>"Alžírsko","AS" =>"Americká Samoa","VI" =>"Americké Panenské ostrovy","AD" =>"Andorra","AO" =>"Angola","AI" =>"Anguilla","AQ" =>"Antarktída","AG" =>"Antigua a Barbuda","AR" =>"Argentína","AM" =>"Arménsko","AW" =>"Aruba","AU" =>"Austrália","AZ" =>"Azerbajdžan","BS" =>"Bahamy","BH" =>"Bahrajn","BD" =>"Bangladéš","BB" =>"Barbados","BE" =>"Belgicko","BZ" =>"Belize","BJ" =>"Benin","BM" =>"Bermudy","BT" =>"Bhután","BY" =>"Bielorusko","BO" =>"Bolívia","BQ" =>"Bonaire, Svätý Eustach a Saba","BA" =>"Bosna a Hercegovina","BW" =>"Botswana","BV" =>"Bouvetov ostrov","BR" =>"Brazília","IO" =>"Britské indickooceánske územie","VG" =>"Britské Panenské ostrovy","BN" =>"Brunej","BG" =>"Bulharsko","BF" =>"Burkina","BI" =>"Burundi","CK" =>"Cookove ostrovy","CW" =>"Curaçao","CY" =>"Cyprus","TD" =>"Čad","CZ" =>"Česko","ME" =>"Čierna Hora","CL" =>"Čile","CN" =>"Čína","DK" =>"Dánsko","DM" =>"Dominika","DO" =>"Dominikánska republika","DJ" =>"Džibutsko","EG" =>"Egypt","EC" =>"Ekvádor","ER" =>"Eritrea","EE" =>"Estónsko","ET" =>"Etiópia","EU" =>"Európska únia","FO" =>"Faerské ostrovy","FK" =>"Falklandy","FJ" =>"Fidži","PH" =>"Filipíny","FI" =>"Fínsko","FR" =>"Francúzsko","GF" =>"Francúzska Guyana","PF" =>"Francúzska Polynézia","TF" =>"Francúzske južné územia","GA" =>"Gabon","GM" =>"Gambia","GH" =>"Ghana","GI" =>"Gibraltár","GR" =>"Grécko","GD" =>"Grenada","GL" =>"Grónsko","GE" =>"Gruzínsko","GP" =>"Guadeloupe","GU" =>"Guam","GT" =>"Guatemala","GG" =>"Guernsey","GN" =>"Guinea","GW" =>"Guinea-Bissau","GY" =>"Guyana","HT" =>"Haiti","HM" =>"Heardov ostrov","NL" =>"Holandsko","AN" =>"Holandské Antily","HN" =>"Honduras","HK" =>"Hongkong","HR" =>"Chorvátsko","IN" =>"India","ID" =>"Indonézia","IR" =>"Irán","IQ" =>"Irak","IE" =>"Írsko","IS" =>"Island","IL" =>"Izrael","JM" =>"Jamajka","JP" =>"Japonsko","YE" =>"Jemen","JE" =>"Jersey","JO" =>"Jordánsko","ZA" =>"Južná Afrika","GS" =>"Južná Georgia a Južné Sandwichove ostrovy","SS" =>"Južný Sudán","KY" =>"Kajmanie ostrovy","KH" =>"Kambodža","CM" =>"Kamerun","CA" =>"Kanada","CV" =>"Kapverdy","QA" =>"Katar","KZ" =>"Kazachstan","KE" =>"Keňa","KG" =>"Kirgizsko","KI" =>"Kiribati","CC" =>"Kokosové ostrovy","CO" =>"Kolumbia","KM" =>"Komory","CG" =>"Kongo","CD" =>"Konžská demokratická republika","KP" =>"Kórejská ľudovodemokratická republika","KR" =>"Kórejská republika","CR" =>"Kostarika","CU" =>"Kuba","KW" =>"Kuvajt","LA" =>"Laos","LS" =>"Lesotho","LB" =>"Libanon","LR" =>"Libéria","LY" =>"Líbya","LI" =>"Lichtenštajnsko","LT" =>"Litva","LV" =>"Lotyšsko","LU" =>"Luxembursko","MO" =>"Macao","MK" =>"Macedónsko","MG" =>"Madagaskar","HU" =>"Maďarsko","MY" =>"Malajzia","MW" =>"Malawi","MV" =>"Maldivy","ML" =>"Mali","MT" =>"Malta","MA" =>"Maroko","MH" =>"Marshallove ostrovy","MQ" =>"Martinik","MU" =>"Maurícius","MR" =>"Mauritánia","YT" =>"Mayotte","UM" =>"Menšie odľahlé ostrovy USA","MX" =>"Mexiko","FM" =>"Mikronézia","MM" =>"Mjanmarsko","MD" =>"Moldavsko","MC" =>"Monako","MN" =>"Mongolsko","MS" =>"Montserrat","MZ" =>"Mozambik","NA" =>"Namíbia","NR" =>"Nauru","DE" =>"Nemecko","NP" =>"Nepál","NE" =>"Niger","NG" =>"Nigéria","NI" =>"Nikaragua","NU" =>"Niue","NF" =>"Norfolk","NO" =>"Nórsko","NC" =>"Nová Kaledónia","NZ" =>"Nový Zéland","OM" =>"Omán","IM" =>"Ostrov Man","PK" =>"Pakistan","PW" =>"Palau","PS" =>"Palestína","PA" =>"Panama","PG" =>"Papua-Nová Guinea","PY" =>"Paraguaj","PE" =>"Peru","PN" =>"Pitcairnove ostrovy","CI" =>"Pobrežie Slonoviny","PL" =>"Poľsko","PR" =>"Portoriko","PT" =>"Portugalsko","AT" =>"Rakúsko","RE" =>"Réunion","GQ" =>"Rovníková Guinea","RO" =>"Rumunsko","RU" =>"Rusko","RW" =>"Rwanda","MF" =>"Saint-Martin","PM" =>"Saint Pierre a Miquelon","SV" =>"Salvádor","WS" =>"Samoa","SM" =>"San Maríno","SA" =>"Saudská Arábia","SN" =>"Senegal","MP" =>"Severné Mariány","SC" =>"Seychely","SL" =>"Sierra Leone","SG" =>"Singapur","SX" =>"Sint Maarten","SK" =>"Slovensko","SI" =>"Slovinsko","SO" =>"Somálsko","AE" =>"Spojené arabské emiráty","GB" =>"Spojené kráľovstvo","US" =>"Spojené štáty","RS" =>"Srbsko","LK" =>"Srí Lanka","CF" =>"Stredoafrická republika","SD" =>"Sudán","SR" =>"Surinam","SH" =>"Svätá Helena","LC" =>"Svätá Lucia","BL" =>"Svätý Bartolomej","KN" =>"Svätý Krištof a Nevis","ST" =>"Svätý Tomáš a Princov ostrov","VC" =>"Svätý Vincent a Grenadíny","SZ" =>"Svazijsko","SY" =>"Sýria","SB" =>"Šalamúnove ostrovy","ES" =>"Španielsko","SJ" =>"Špicbergy a Jan Mayen","CH" =>"Švajčiarsko","SE" =>"Švédsko","TJ" =>"Tadžikistan","TW" =>"Taiwan","IT" =>"Taliansko","TZ" =>"Tanzánia","TH" =>"Thajsko","TG" =>"Togo","TK" =>"Tokelau","TO" =>"Tonga","TT" =>"Trinidad a Tobago","TN" =>"Tunisko","TR" =>"Turecko","TM" =>"Turkménsko","TC" =>"Turks a Caicos","TV" =>"Tuvalu","UG" =>"Uganda","UA" =>"Ukrajina","UY" =>"Uruguaj","UZ" =>"Uzbekistan","VU" =>"Vanuatu","VA" =>"Vatikán","VE" =>"Venezuela","CX" =>"Vianočný ostrov","VN" =>"Vietnam","TL" =>"Východný Timor","WF" =>"Wallis a Futuna","ZM" =>"Zambia","EH" =>"Západná Sahara","ZW" =>"Zimbabwe"),
            'en' => array("AF" =>"Afghanistan","AX" =>"Aland Islands","AL" =>"Albania","DZ" =>"Algeria","AS" =>"American Samoa","AD" =>"Andorra","AO" =>"Angola","AI" =>"Anguilla","AQ" =>"Antarctica","AG" =>"Antigua and Barbuda","AR" =>"Argentina","AM" =>"Armenia","AW" =>"Aruba","AU" =>"Australia","AT" =>"Austria","AZ" =>"Azerbaijan","BS" =>"Bahamas","BH" =>"Bahrain","BD" =>"Bangladesh","BB" =>"Barbados","BY" =>"Belarus","BE" =>"Belgium","BZ" =>"Belize","BJ" =>"Benin","BM" =>"Bermuda","BT" =>"Bhutan","BO" =>"Bolivia","BQ" =>"Bonaire, Sint Eustatius and Saba","BA" =>"Bosnia and Herzegovina","BW" =>"Botswana","BV" =>"Bouvet Island","BR" =>"Brazil","IO" =>"British Indian Ocean Territory","BN" =>"Brunei Darussalam","BG" =>"Bulgaria","BF" =>"Burkina Faso","BI" =>"Burundi","CV" =>"Cabo Verde","KH" =>"Cambodia","CM" =>"Cameroon","CA" =>"Canada","KY" =>"Cayman Islands","CF" =>"Central African Republic","TD" =>"Chad","CL" =>"Chile","CN" =>"China","CX" =>"Christmas Island","CC" =>"Cocos (Keeling) Islands","CO" =>"Colombia","KM" =>"Comoros","CG" =>"Congo","CD" =>"Congo (Democratic Republic of the)","CK" =>"Cook Islands","CR" =>"Costa Rica","CI" =>"Cote d'Ivoire","HR" =>"Croatia","CU" =>"Cuba","CW" =>"Curacao","CY" =>"Cyprus","CZ" =>"Czechia","DK" =>"Denmark","DJ" =>"Djibouti","DM" =>"Dominica","DO" =>"Dominican Republic","EC" =>"Ecuador","EG" =>"Egypt","SV" =>"El Salvador","GQ" =>"Equatorial Guinea","ER" =>"Eritrea","EE" =>"Estonia","ET" =>"Ethiopia","FK" =>"Falkland Islands (Malvinas)","FO" =>"Faroe Islands","FJ" =>"Fiji","FI" =>"Finland","FR" =>"France","GF" =>"French Guiana","PF" =>"French Polynesia","TF" =>"French Southern Territories","GA" =>"Gabon","GM" =>"Gambia","GE" =>"Georgia","DE" =>"Germany","GH" =>"Ghana","GI" =>"Gibraltar","GR" =>"Greece","GL" =>"Greenland","GD" =>"Grenada","GP" =>"Guadeloupe","GU" =>"Guam","GT" =>"Guatemala","GG" =>"Guernsey","GN" =>"Guinea","GW" =>"Guinea-Bissau","GY" =>"Guyana","HT" =>"Haiti","HM" =>"Heard Island and McDonald Islands","VA" =>"Holy See","HN" =>"Honduras","HK" =>"Hong Kong","HU" =>"Hungary","IS" =>"Iceland","IN" =>"India","ID" =>"Indonesia","IR" =>"Iran (Islamic Republic of)","IQ" =>"Iraq","IE" =>"Ireland","IM" =>"Isle of Man","IL" =>"Israel","IT" =>"Italy","JM" =>"Jamaica","JP" =>"Japan","JE" =>"Jersey","JO" =>"Jordan","KZ" =>"Kazakhstan","KE" =>"Kenya","KI" =>"Kiribati","KP" =>"Korea (Democratic People's Republic of)","KR" =>"Korea (Republic of)","KW" =>"Kuwait","KG" =>"Kyrgyzstan","LA" =>"Lao People's Democratic Republic","LV" =>"Latvia","LB" =>"Lebanon","LS" =>"Lesotho","LR" =>"Liberia","LY" =>"Libya","LI" =>"Liechtenstein","LT" =>"Lithuania","LU" =>"Luxembourg","MO" =>"Macao","MK" =>"Macedonia (the former Yugoslav Republic of)","MG" =>"Madagascar","MW" =>"Malawi","MY" =>"Malaysia","MV" =>"Maldives","ML" =>"Mali","MT" =>"Malta","MH" =>"Marshall Islands","MQ" =>"Martinique","MR" =>"Mauritania","MU" =>"Mauritius","YT" =>"Mayotte","MX" =>"Mexico","FM" =>"Micronesia (Federated States of)","MD" =>"Moldova (Republic of)","MC" =>"Monaco","MN" =>"Mongolia","ME" =>"Montenegro","MS" =>"Montserrat","MA" =>"Morocco","MZ" =>"Mozambique","MM" =>"Myanmar","NA" =>"Namibia","NR" =>"Nauru","NP" =>"Nepal","NL" =>"Netherlands","NC" =>"New Caledonia","NZ" =>"New Zealand","NI" =>"Nicaragua","NE" =>"Niger","NG" =>"Nigeria","NU" =>"Niue","NF" =>"Norfolk Island","MP" =>"Northern Mariana Islands","NO" =>"Norway","OM" =>"Oman","PK" =>"Pakistan","PW" =>"Palau","PS" =>"Palestine, State of","PA" =>"Panama","PG" =>"Papua New Guinea","PY" =>"Paraguay","PE" =>"Peru","PH" =>"Philippines","PN" =>"Pitcairn","PL" =>"Poland","PT" =>"Portugal","PR" =>"Puerto Rico","QA" =>"Qatar","RE" =>"Reunion","RO" =>"Romania","RU" =>"Russian Federation","RW" =>"Rwanda","BL" =>"Saint Barthelemy","SH" =>"Saint Helena, Ascension and Tristan da Cunha","KN" =>"Saint Kitts and Nevis","LC" =>"Saint Lucia","MF" =>"Saint Martin (French part)","PM" =>"Saint Pierre and Miquelon","VC" =>"Saint Vincent and the Grenadines","WS" =>"Samoa","SM" =>"San Marino","ST" =>"Sao Tome and Principe","SA" =>"Saudi Arabia","SN" =>"Senegal","RS" =>"Serbia","SC" =>"Seychelles","SL" =>"Sierra Leone","SG" =>"Singapore","SX" =>"Sint Maarten (Dutch part)","SK" =>"Slovakia","SI" =>"Slovenia","SB" =>"Solomon Islands","SO" =>"Somalia","ZA" =>"South Africa","GS" =>"South Georgia and the South Sandwich Islands","SS" =>"South Sudan","ES" =>"Spain","LK" =>"Sri Lanka","SD" =>"Sudan","SR" =>"Suriname","SJ" =>"Svalbard and Jan Mayen","SZ" =>"Swaziland","SE" =>"Sweden","CH" =>"Switzerland","SY" =>"Syrian Arab Republic","TW" =>"Taiwan, Province of China[a]","TJ" =>"Tajikistan","TZ" =>"Tanzania, United Republic of","TH" =>"Thailand","TL" =>"Timor-Leste","TG" =>"Togo","TK" =>"Tokelau","TO" =>"Tonga","TT" =>"Trinidad and Tobago","TN" =>"Tunisia","TR" =>"Turkey","TM" =>"Turkmenistan","TC" =>"Turks and Caicos Islands","TV" =>"Tuvalu","UG" =>"Uganda","UA" =>"Ukraine","AE" =>"United Arab Emirates","GB" =>"United Kingdom of Great Britain and Northern Ireland","US" =>"United States of America","UM" =>"United States Minor Outlying Islands","UY" =>"Uruguay","UZ" =>"Uzbekistan","VU" =>"Vanuatu","VE" =>"Venezuela (Bolivarian Republic of)","VN" =>"Viet Nam","VG" =>"Virgin Islands (British)","VI" =>"Virgin Islands (U.S.)","WF" =>"Wallis and Futuna","EH" =>"Western Sahara","YE" =>"Yemen","ZM" =>"Zambia","ZW" =>"Zimbabwe")
        );

        return array_key_exists($this->Country, $countries['sk']) ? $countries['sk'][$this->Country] : null;
    }
}