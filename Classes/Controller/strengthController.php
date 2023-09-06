<?php

    namespace WebanUg\NwtuStrength\Controller;

    use Doctrine\DBAL\DBALException;
    use Doctrine\DBAL\Driver\Exception;
    use TYPO3\CMS\Core\Context\Context;
    use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
    use TYPO3\CMS\Core\Utility\GeneralUtility;
    use TYPO3\CMS\Core\Database\ConnectionPool;
    use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

    class strengthController extends ActionController
    {
        public function __construct()
        {
        }

        function strengthAction(): string
        {
            if ( $_POST )
            {
                $_SESSION['staerkemeldung'] = $_POST;
            }
            $html = "";


            switch ( $_POST['action'] )
            {
                case "send_meldung":
                    // Eingaben prüfen
                    $err = 0;
                    $errFlds = array();
                    foreach ( $_POST['req'] as $name )
                    {
                        if ( !$_POST[ $name ] )
                        {
                            $err++;
                            $errFlds[] = $name;
                        }
                    }
                    if ( $err > 0 && is_array( $errFlds ) )
                    {
                        $html .= "<p>FEHLER: Sie haben nicht alle Pflichtfelder ausgefüllt: ";
                        foreach ( $errFlds as $name )
                        {
                            $html .= "<br>" . $name;
                        }
                        $html .= "<br><br><a href='/?id=75'>Zur Stärkemeldung</a>";
                        break;
                    }

                    $item = "";
                    $to = "office@nwtu.de";

                    $from = $_POST['Email1'];
                    $subject = "Stärkemeldung über Online-Formular";

                    $header = ( "From: " . $from . "\n" );
                    $header .= ( "Reply-To: " . $from . "\n" );
                    $header .= ( "Return-Path: " . $from . "\n" );
                    $header .= ( "X-Mailer: PHP/" . phpversion() . "\n" );
                    $header .= ( "X-Sender-IP: " . $_SERVER['REMOTE_ADDR'] . "\n" );
                    $header .= ( "Content-type: text/html; charset=\"utf-8\"\r\n" );

                    $msg = "";
                    foreach ( $_POST as $key => $value )
                    {
                        if ( !is_array( $value ) && $key != "action" )
                        {
                            $msg .= "<br />" . $key . ": " . $value;
                        }
                    }

                    if ( mail( $to, $subject, $item . "<br /><br />" . $msg, $header ) )
                    {
                        $html = "<p>Vielen Dank für Ihre Stärkemeldung. Wir werden sie schnellstmöglich bearbeiten.</p>";
                    }
                    else
                    {
                        $html = "<p>FEHLER: Es ist ein Problem beim Versenden Ihrer Stärkemeldung aufgetreten. Bitte probieren Sie es noch einmal.</p>";
                    }
                    break;
                default:
                    $html .= $this->showFormular();
                    break;
            }
            return $html;

        }

        function showFormular(): string
        {
            $now = time();
            //$datum = date( "d.m.Y", time() );
            $currYear = date( "Y", time() );

            /* Nur für die Jahresänderung zu 2021 siehe E-Mail vom 15.10.2020 */


            $nextYear = $currYear;
            $deadline_m = "01";
            $deadline_d = "01";
            $deadline_y = "2024";
            $deadline = strtotime( $deadline_y . "-" . $deadline_m . "-" . $deadline_d );

            if ( ( $now <= $deadline ) )
            {
                $nextYear = $deadline_y;
                //$nextYear = $currYear;
            }

            $mitgliedsBeitrag = 8;
            $gesamtbetrag_DTU_NWTU = 300;
            $gesamtbetragNeuaufnahme = 22.1;
            $zusatzBeitrag = 2;
            $vk_pauschale = 5.5;


            $html = "";
            if ( is_array( $_SESSION['staerkemeldung'] ) )
            {
                $html .= '<script>
					$(document).ready(function(){

					';

                foreach ( $_SESSION['staerkemeldung'] as $key => $value )
                {
                    // neue Variable mit dem Namen des Keys erstellen.
                    $$key = $value;
                    $html .= "\n\n
					$('input[name=\"" . $key . "\"]').val(\"" . $value . "\");
				";
                }
                $html .= "
					
				});
				</script>";

            }

            $html .= '

<form style="width:100%" id="meldung" name="meldung" method="post" action="' . $_SERVER["REQUEST_URI"] . '">
<h2>Stärkemeldung Taekwondo ' . $nextYear . ' (Einsendeschluß ' . $deadline_d . '.' . $deadline_m . '.' . $deadline_y . ')</h2>

<style>
.meldung {
    width:100%;
	background:#fdfdfd;
}
.meldung * {
    font-size:14px;
}
.meldung td {
    text-align:left;
    vertical-align:middle;
    padding:5px;
}
.meldung input[type=text] {
    width:100%;
}
.meldung .yellow {
    background-color:#f9d408;
}
.m_anzahl,.w_anzahl, .neueintritte_anzahl {
    width:30px!important;
    text-align:center;
}
.center {
    text-align:center!important;
}
.right {
    text-align:right!important;
}
#user_confirm_strength {
	float:left;
	margin:0 10px 0 0;
}
</style>

<div style="width:50%;float:left;">
<table border="1" cellspacing="0" cellpadding="5" class="meldung">
    <tr>
        <td colspan="4"><strong>Vereinsdaten</strong></td>
    </tr>
    <tr>
        <td style="width:20%;">Vereinsname:*</td>
        <td colspan="3">
            <input class="yellow vereinsname" type="text" name="Vereinsname" value="" />
            <input type="hidden" name="req[]" value="Vereinsname" />
        </td>
    </tr>
    <tr>
        <td>Vereinsnummer NWTU:*</td>
        <td colspan="3">
            <input id="Vereinsnummer_NWTU" class="yellow" type="text" name="Vereinsnummer_NWTU" value="" />
            <input type="hidden" name="req[]" value="Vereinsnummer_NWTU" />
        </td>
    </tr>        
    <tr>
        <td>LSB-Nummer:*</td>
        <td>
            <input class="yellow" type="text" name="LSB_Nummer" value="" />
            <input type="hidden" name="req[]" value="LSB_Nummer" />
        </td>
        <td>Gründungsjahr</td>
        <td><input class="yellow" type="text" name="Gruendungsjahr" value="" /></td>
    </tr>        
    <tr>
        <td>Telefon:*</td>
        <td>
            <input class="yellow" type="text" name="Telefon" value="" />
            <input type="hidden" name="req[]" value="Telefon" />
        </td>
        <td>DTU Datenbank Nr.:*</td>
        <td>
            <input class="yellow" type="text" name="DTU_Datenbanknr" value="" />
            <input type="hidden" name="req[]" value="DTU_Datenbanknr" />
        </td>
    </tr>        
    <tr>
        <td>Mobil:</td>
        <td colspan="3"><input class="yellow" type="text" name="Mobilnummer" value="" /></td>
    </tr>
    <tr>
        <td>Fax:</td>
        <td colspan="3"><input class="yellow" type="text" name="Fax" value="" /></td>
    </tr>
</table>
</div>
<div style="width:50%;float:left;">
<table border="1" cellspacing="0" cellpadding="5" class="meldung">
    <tr>
        <td colspan="4"><strong>Adressdaten</strong></td>
    </tr>
    <tr>
        <td>Abt. Leiter:*</td>
        <td colspan="3">
            <input class="yellow" type="text" name="Abt_Leiter" value="" />
            <input type="hidden" name="req[]" value="Abt_Leiter" />
        </td>
    </tr>
    <tr>
        <td>Straße/Nr.:*</td>
        <td colspan="3">
            <input class="yellow Strasse_Nr" type="text" name="Strasse_Nr" value="" />
            <input type="hidden" name="req[]" value="Strasse_Nr" />
        </td>
    </tr>
    <tr>
        <td>PLZ/Ort:*</td>
        <td colspan="3">
            <input id="PLZ_Ort" class="yellow" type="text" name="PLZ_Ort" value="" />
            <input type="hidden" name="req[]" value="PLZ_Ort" />
        </td>
    </tr>
    <tr>
        <td>E-Mail 1:*</td>
        <td colspan="3">
            <input class="yellow" type="text" name="Email1" value="" />
            <input type="hidden" name="req[]" value="Email1" />
        </td>
    </tr>
    <tr>
        <td>E-Mail 2:</td>
        <td colspan="3"><input class="yellow" type="text" name="Email2" value="" /></td>
    </tr>
    <tr>
        <td>Internet:</td>
        <td colspan="3"><input class="yellow" type="text" name="Internet" value="" /></td>
    </tr>
</table>
</div>
<table border="1" cellspacing="0" cellpadding="5" class="meldung">
    <tr>
        <td colspan="4">
            <p><strong>Mitgliederstärke laut LSB-Meldung</strong><br />
                Die Mitgliederzahlen müssen mit den Zahlen der LSB-Meldung ' . $nextYear . ' übereinstimmen!
            </p>
        </td>
    </tr>
</table>
<table border="1" cellspacing="0" cellpadding="5" class="meldung">
    <tr>
        <th class="center">Alter</th>
        <th class="center">bis 6 Jahre</th>
        <th class="center">7-14 Jahre</th>
        <th class="center">15-18 Jahre</th>
        <th class="center">19-26 Jahre</th>
        <th class="center">27-40 Jahre</th>
        <th class="center">41-60 Jahre</th>
        <th class="center">über 61 Jahre</th>
        <th class="center">gesamt:</th>
        <th class="center">Mitglieder insgesamt:</th>
        <th class="center">Davon Eintritte zum 01.01.' . $nextYear . '</th>
    </tr>
    <tr>
        <td><strong>männlich</strong></td>
        <td class="center"><input class="m_anzahl yellow" type="text" name="m_bis_6_Jahre" value="" /></td>
        <td class="center"><input class="m_anzahl yellow" type="text" name="m_7_bis_14_Jahre" value="" /></td>
        <td class="center"><input class="m_anzahl yellow" type="text" name="m_15_bis_18_Jahre" value="" /></td>
        <td class="center"><input class="m_anzahl yellow" type="text" name="m_19_bis_26_Jahre" value="" /></td>
        <td class="center"><input class="m_anzahl yellow" type="text" name="m_27_bis_40_Jahre" value="" /></td>
        <td class="center"><input class="m_anzahl yellow" type="text" name="m_41_bis_60_Jahre" value="" /></td>
        <td class="center"><input class="m_anzahl yellow" type="text" name="m_ueber_61_Jahre" value="" /></td>
        <td class="center"><span id="m_gesamt_anzahl">0</span></td>
        <td rowspan="2" class="center"><span id="gesamtMitglieder">0</span></td>
        <td rowspan="2" class="center"><input class="neueintritte_anzahl yellow" type="text" name="neueintritte_anzahl" value="" /></td>
    </tr>
    <tr>
        <td><strong>weiblich</strong></td>
        <td class="center"><input class="w_anzahl yellow" type="text" name="w_bis_6_Jahre" value="" /></td>
        <td class="center"><input class="w_anzahl yellow" type="text" name="w_7_bis_14_Jahre" value="" /></td>
        <td class="center"><input class="w_anzahl yellow" type="text" name="w_15_bis_18_Jahre" value="" /></td>
        <td class="center"><input class="w_anzahl yellow" type="text" name="w_19_bis_26_Jahre" value="" /></td>
        <td class="center"><input class="w_anzahl yellow" type="text" name="w_27_bis_40_Jahre" value="" /></td>
        <td class="center"><input class="w_anzahl yellow" type="text" name="w_41_bis_60_Jahre" value="" /></td>
        <td class="center"><input class="w_anzahl yellow" type="text" name="w_ueber_61_Jahre" value="" /></td>
        <td class="center"><span id="w_gesamt_anzahl">0</span></td>
    </tr>
    <tr>
        <td colspan="5" rowspan="2"><strong>Ermittlung des Mitgliedsbeitrages</strong></td>
        <td colspan="3" rowspan="2"><strong>Aufnahmegebühr zum 01.01.' . $nextYear . '</strong></td>
        <td colspan="2">Gesamtbetrag DTU/NWTU</td>
        <td class="right"><span id="gesamtbeitrag_DTU_NWTU_final">0</span>&nbsp;€</td>
    </tr>
    <tr>
        <td colspan="2">Gesamtbetrag Neuaufnahme</td>
        <td class="right"><span id="gesamtbetragNeuaufnahme_summe">0</span>&nbsp;€</td>
    </tr>
    <tr>
        <td class="center">Anzahl der Mitglieder</td>
        <td class="center">x</td>
        <td class="center">Summe</td>
        <td class="center">Vereinsbeitrag DTU</td>
        <td class="center">Gesamtbeitrag DTU/NWTU</td>
        <td class="center">Anzahl</td>
        <td class="center">x</td>
        <td class="center">Gesamtbeitrag Neuaufnahme</td>
        <td class="center" colspan="2">Versandkostenpauschale</td>
        <td class="right">' . number_format( $vk_pauschale, 2, ",", "." ) . '&nbsp;€</td>
    </tr>
    <tr>
        <td class="center"><span id="gesamtMitglieder_summe">0</span></td>
        <td class="center">' . number_format( $mitgliedsBeitrag, 2, ",", "." ) . '&nbsp;€</td>
        <td class="center"><span id="mitgliedsBeitrag_summe"></span>&nbsp;€</td>
        <td class="center">' . number_format( $gesamtbetrag_DTU_NWTU, 2, ",", "." ) . '&nbsp;€</td>
        <td class="center"><span id="gesamtbeitrag_DTU_NWTU_summe">0,00 €</span></td>
        <td class="center"><span id="neueintritte_anzahl">0</span></td>
        <td class="center">' . number_format( $gesamtbetragNeuaufnahme, 2, ",", "." ) . '&nbsp;€</td>
        <td class="center"><span id="gesamtBeitrag_Neuaufnahme">0</span></td>
        <td colspan="3"></td>
    </tr>
    <tr>
        <td></td>
        <td class="center">' . number_format( $zusatzBeitrag, 2, ",", "." ) . ' €</td>
        <td class="center"><span id="zusatzbeitrag_summe">0</span></td>
        <td colspan="2" class="center">Zusatzbeitrag DTU ' . number_format( $zusatzBeitrag, 2, ",", "." ) . '&nbsp;€ pro Mitglied</td>
        <td colspan="3"></td>
        <td class="right" colspan="2"><strong>Gesamtbetrag:</strong></td>
        <td class="right"><strong><span id="gesamtBetrag"></span> €</strong></td>
    </tr>
    <tr>
        <td colspan="11">
            <strong><ins>SEPA Lastschriftmandat:</ins></strong><br />
            <p>
                Die NWTU hat ihre Beitragseinzüge auf das neue SEPA-Lastschriftverfahren umgestellt. Dazu ist es erforderlich, dem Verband ein sog. Lastschriftmandat zu erteilen,
                durch das Sie die künftigen Beitragsabbuchungen von Ihrem Konto autorisieren.<br>
                Sie benötigen dafür die BIC und IBAN Ihres Kontos.
            </p>
        </td>
    </tr>
</table>
<div style="float:left;width:50%;">
    <table border="1" cellspacing="0" cellpadding="5" class="meldung">
        <tr>
            <td><strong>Name und Anschrift des Zahlungsempfängers</strong></td>
        </tr>
        <tr>
            <td>Nordrhein-Westfälische Taekwondo Union e.V.</td>
        </tr>
        <tr>
            <td>Hindenburgstr. 28</td>
        </tr>
        <tr>
            <td>51766 Engelskirchen</td>
        </tr>
        <tr>
            <td><strong>Gläubiger Identifikationsnummer:</strong> DE64ZZZ00000747851</td>
        </tr>
    </table>
</div>
<div style="float:left;width:50%;">
    <table border="1" cellspacing="0" cellpadding="5" class="meldung">
        <tr>
            <td><strong>Name und Anschrift des Kontoinhabers</strong></td>
        </tr>
        <tr>
            <td>
                <span><input class="" type="text" name="Kontoinhaber" value="" placeholder="Name des Kontoinhabers" /></span>
            </td>
        </tr>
        <tr>
            <td><span id="Strasse_Nr">&nbsp;</span></td>
        </tr>
        <tr>
            <td><span class="plz_ort">&nbsp;</span></td>
        </tr>
        <tr>
            <td>NWTU-Vereinsnummer: <span id="vereinsnummer_nwtu_final"></span></td>
        </tr>
    </table>
</div>
<table border="1" cellspacing="0" cellpadding="5" class="meldung">
    <tr>
        <td>
            <strong><ins>Andere Zahlungsarten</ins></strong>
            <p>
		Sie können auch per Überweisung bezahlen, dafür lassen Sie einfach die Felder zur Bankverbindung frei.
            </p>

            <strong><ins>SEPA-Lastschriftmandat</ins></strong>
            <p>
                Ich/Wir ermächtige(n) Sie, Zahlungen von meinem/unserem Konto mittels Lastschrift einzuziehen. Zugleich weise(n) ich/wir mein/unser Kreditinstitut an, die 
                von der NWTU e.V. auf mein/unser Konto gezogenen Lastschriften einzulösen.
            </p>
            <p>
                Der Gesamtbetrag wird von uns nach Rechnungserhalt (spätestens bis zum 31.03.' . $nextYear . ') überwiesen, die JSM und Pässer werden nach Zahlungseingang versendet.
            </p>
        </td>
    </tr>
</table>
<table border="1" cellspacing="0" cellpadding="5" class="meldung">
    <tr>
        <td>Gesamtbetrag:</td>
        <td><span id="gesamtBetrag_final">0</span>&nbsp;€</span>
            <input type="hidden" id="gesamtBetrag_summe" name="gesamtBetrag_summe" value="" />
        </td>
        
        <td>IBAN:* </td>
        <td>
            <input class="" type="text" name="IBAN" value="" />
        </td>
    </tr>
    <tr>
        <td>BIC:*</td>
        <td><input class="" type="text" name="BIC" value="" /></td>
        
        <td>Institut:*</td>
        <td>
            <input class="" type="text" name="Institut" value="" />
        </td>
    </tr>
    <tr>
        <td>Kontoinhaber(Verein):</td>
        <td>
            <span class="Kontoinhaber_Verein"></span>
            <input id="Kontoinhaber_Verein" type="hidden" value="Kontoinhaber_Verein" value="" readonly="readonly" /></td>
        
        <td></td>
        <td></td>
    </tr>

	<tr>
		<td colspan="4">
			<input type="checkbox" name="Datenschutzbestätigung" id="user_confirm_strength" class="req yellow" />
			<input type="hidden" name="req[]" value="Datenschutzbestätigung" />
			<p><label for="user_confirm_strength">Ich stimme zu, dass meine Angaben aus dem Formular zur Beantwortung
meiner Anfrage erhoben und verarbeitet und gespeichert werden.
<br>
Hinweis: Sie können Ihre Einwilligung jederzeit für die Zukunft per E-Mail an dsb@nwtu.de widerrufen.
Detaillierte Informationen zum Umgang mit Nutzerdaten finden Sie in unserer
Datenschutzerklärung</label></p>

		</td>
	</tr>

    <tr>
        <td colspan="2"><input id="refresh" type="button" value="Daten aktualisieren" /></td>
        <td colspan="2">
            <input type="hidden" name="action" value="send_meldung" />
            <input id="submitButton" type="button" value="Stärkemeldung absenden" />
        </td>
    </tr>
</table>
</form>
';
            return $html;
        }
    } // end class

