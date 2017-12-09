<?php
ini_set('max_execution_time', 300);
session_start();

$nbuServiceUrl = "https://bank.gov.ua/NBU_BankInfo/get_data_branch";

// Use one of this
$googleApiKey1 = "AIzaSyC65ozaddoqEKT5Kdi7GSzz-G4Uz7ekDIE";
$googleApiKey2 = "AIzaSyDuDdSUa9l1nrtNUVkuXxEE6TN4dd-nKjo";

$googleGeocodeUrl = "https://maps.googleapis.com/maps/api/geocode/json?&language=uk&key=" . $googleApiKey2 . "&address=";

// Available params: glmfo and typ https://bank.gov.ua/NBU_BankInfo/get_data_branch?typ=0

$typ = Array(
	"банк-юридична особа",
	"філія",
	"відділення",
	"представництво на території України",
	"обмінний пункт",
	"філія за межами України",
	"представництво за межами України"
	);
// use $regions plus word "область"

$regions = Array(
	"Не визначена",
	"Вінницька",
	"Волинська",
	"Дніпропетровська",
	"Донецька",
	"Житомирська",
	"Закарпатська",
	"Запорізька",
	"Івано-Франківська",
	"Київська",
	"Кіровоградська",
	"Автономна Республiка Крим",
	"Луганська",
	"Львівська",
	"Миколаївська",
	"Одеська",
	"Полтавська",
	"Рівненська",
	"Сумська",
	"Тернопільська",
	"Харківська",
	"Херсонська",
	"Хмельницька",
	"Черкаська",
	"Чернігівська",
	"Чернівецька",
	"Київ",
	"Севастополь"
	);

$status = Array(
	"Режим ліквідації",
	"Нормальний"
	);

// $testUrl = $nbuServiceUrl . "?typ=0";

// https://maps.googleapis.com/maps/api/geocode/json?language=uk&address=вул.Чорновола,27,Рівне&key=AIzaSyC65ozaddoqEKT5Kdi7GSzz-G4Uz7ekDIE

// Use $testUrl

$banks = simplexml_load_string(file_get_contents(__DIR__."/cache/sample.xml"));

$_SESSION["geobank"] = Array();

$odd = Array('будинок', '(ЛІТЕРА В)', 'ПАТ', 'ПУАТ', 'АКБ', 'АБ', 'КБ', '"');

// you can use $status array or property KSTAN (true) as normal

foreach ($banks->ROW as $bank) {

	$status = intval($bank->KSTAN);

	if ( $status === 1) {

		$bankName = $bank->SHORTNAME;
		$bankName = str_replace($odd, '', $bankName);
		$bankName = strtoupper(trim($bankName));

		$geoUrl = $googleGeocodeUrl . urlencode(trim($bank->ADRESS)) . "," . 
									  urlencode(trim($bank->NP)) . "," . 
									  urlencode("Україна")
									  ;
		
		$geo = json_decode(file_get_contents( $geoUrl ), true);
		
		if ( !empty($geo["results"]) ) {
			
			foreach ($geo["results"] as $result) {
				$bankLat = $result["geometry"]["location"]["lat"];
				$bankLng = $result["geometry"]["location"]["lng"];
				$formatted_address = $result["formatted_address"];
			}

			$_SESSION["geobank"][] = Array(
			$bankName,
			$bankLat,
			$bankLng,
			$formatted_address
			);
		}
	}
}

//echo json_encode($_SESSION["geobank"], JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT);

?>
<!DOCTYPE html>
<html>
  <head>
    <title>Map</title>
    <meta name="viewport" content="initial-scale=1.0">
    <meta charset="utf-8">
    <style>
      /* Always set the map height explicitly to define the size of the div
       * https://developers.google.com/maps/documentation/javascript/tutorial?csw=1
       * element that contains the map. */
      #map {
        height: 100%;
      }
      /* Optional: Makes the sample page fill the window. */
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
    </style>
  </head>
  <body>
    <div id="map"></div>
    <script>

      var map;

      function initMap() {
      	
      	var banks = <?= json_encode($_SESSION["geobank"], JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT) ?>;
        
        var map = new google.maps.Map(document.getElementById('map'), {
          center: {lat: 50.441920, lng: 30.513209},
          zoom: 13
        });
        
        for (var i = 0; i < banks.length; i++) {
          
          var bank = banks[i];

	      let marker = new google.maps.Marker({
	          position: {lat: bank[1], lng: bank[2]},
	          map: map,
	          title: bank[0],
	          animation: google.maps.Animation.DROP
	       });

			let contentString = '<div id="content">'+
			      '<div id="siteNotice">'+
			      '</div>'+
			      '<h4 id="firstHeading" class="firstHeading">'+bank[0]+'</h4>'+
			      '<div id="bodyContent">'+
			      '<p>Адреса: '+bank[3]+'</p>'+
			      '</div>'+
			      '</div>';

		    let infowindow = new google.maps.InfoWindow({
		      content: contentString,
		      maxWidth: 250
		    });

			marker.addListener('click', function() {
		      infowindow.open(map, marker);
		    });

        }; 

        marker.addListener('click', toggleBounce);

      }
	
		function toggleBounce() {
		  if (marker.getAnimation() !== null) {
		    marker.setAnimation(null);
		  } else {
		    marker.setAnimation(google.maps.Animation.BOUNCE);
		  }
		}

    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= $googleApiKey2 ?>&callback=initMap"
    async defer></script>
  </body>
</html>
<?php session_destroy(); ?>