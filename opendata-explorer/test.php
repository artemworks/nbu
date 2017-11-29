<?php
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
@ob_end_clean();
set_time_limit(0);
?>
<?php ini_set('display_errors', '1');  ?>
<?php require_once("includes/layout/header.php"); ?>
<div class="col-md-12">
	<small>Check date: <?= date('d.m.Y') ?></small>
	
	<h1>Datasets availability check</h1>
	<table class="table">
	<thead>
		<tr>
			<td>Num</td>
			<td>Human Name</td>
			<td>Machine Name</td>
			<td>Periods (freq)</td>
			<td>Availability</td>
			<td>start=, end=</td>
			<td>freq=</td>
			<td>leveli=1</td>
		</tr>
	</thead>
	<?php
		$statDirectory = "https://bank.gov.ua/NBUStatService/v1/statdirectory/";
		$decodedDirectoryContent = json_decode(file_get_contents($statDirectory . "?json"));
		
		$num = 0;
		
		foreach ($decodedDirectoryContent as $key => $value) {
			$num++;

			// General availability of objects/datasets in Statdirectory
			$urlDataset = $statDirectory . $value->apikod;
			$urlHeadersResponse = get_headers($urlDataset . "?json")[0];

			// start= & end=
			$urlDatasetStartDate = $urlDataset . "?start=20170401&end=20170701";
			$urlHeadersResponseStartDate = get_headers($urlDatasetStartDate . "&json")[0];

			// freq=
			$arrFreq = explode(",", $value->periods);
			$urlDatasetFreq = $urlDatasetStartDate . "&freq=" . $arrFreq[0];
			$urlHeadersResponseFreq = get_headers($urlDatasetStartDate . "&json")[0];

			// leveli=1
			$urlDatasetLeveli = $urlDatasetStartDate . "&leveli=1";
			$urlHeadersResponseLeveli = get_headers($urlDatasetLeveli . "&json")[0];


			echo "<tr>";
			echo "<td>" . $num . "</td>" .
				 "<td>" . $value->txt . "</td>" . 
				 "<td>" . $value->apikod . "</td>" . 
				 "<td>" . $value->periods . "</td>";

			echo "<td>";
				 if ($urlHeadersResponse === "HTTP/1.1 200 OK") { echo '<i class="fa fa-check" aria-hidden="true"></i>'; } 
				 else { echo '<i class="fa fa-times" aria-hidden="true"></i>'; }
			echo "</td>";


			echo "<td>";
				 if ($urlHeadersResponseStartDate === "HTTP/1.1 200 OK") { echo '<i class="fa fa-check" aria-hidden="true"></i>'; } 
				 else { echo '<i class="fa fa-times" aria-hidden="true"></i>'; }
			echo "</td>";

			echo "<td>";
				 if ($urlHeadersResponseFreq === "HTTP/1.1 200 OK") { echo '<i class="fa fa-check" aria-hidden="true"></i>'; } 
				 else { echo '<i class="fa fa-times" aria-hidden="true"></i>'; }
			echo "</td>";

			echo "<td>";
				 if ($urlHeadersResponseLeveli === "HTTP/1.1 200 OK") { echo '<i class="fa fa-check" aria-hidden="true"></i>'; } 
				 else { echo '<i class="fa fa-times" aria-hidden="true"></i>'; }
			echo "</td>";

			echo "</tr>";
		}
	?>
	</table>

	<h1>Dimensions availability check</h1>
	<table class="table">
	<?php
		$dimensionDirectory = $statDirectory . "dimension";
		$decodedDirectoryContent = json_decode(file_get_contents($dimensionDirectory . "?json"));

		foreach ($decodedDirectoryContent as $key => $value) {
			$urlDataset = $dimensionDirectory . "/" . $value->dimensionkod . "?json";
			$urlHeadersResponse = get_headers($urlDataset)[0];

			echo "<tr>";
			echo "<td>" . $value->txt . "</td>" . 
				 "<td>" . $value->dimensionkod . "</td>" . 
				 "<td>";
				 if ($urlHeadersResponse === "HTTP/1.1 200 OK") { echo '<i class="fa fa-check" aria-hidden="true"></i>'; } 
				 else { echo '<i class="fa fa-times" aria-hidden="true"></i>'; }
			echo "</td></tr>";

		}
	?>
	</table>
</div>
<?php require_once("includes/layout/footer.php"); ?>
<!-- Developed by Artem Rumiantsev -->