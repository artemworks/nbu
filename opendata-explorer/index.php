<?php
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
@ob_end_clean();
set_time_limit(0);
?>
<?php ini_set('display_errors', '1');  ?>
<?php require_once("includes/fusioncharts.php"); ?>
<?php require_once("includes/layout/header.php"); ?>
<?php
	$caching = 0;
?>

		<div class="col-md-4">

			<form method="GET">

			<?php

			/* 
			Entry point. Loading all datasets available at the moment.
			Checking object properties to use them for construction of the form:
			- txt (dataset human name)
			- apikod (dataset machine name)
			- periods (periodicity)
			- dimensions (possible parameters)
			- entrydate (min available date)
			*/

			$entryPoint = "https://bank.gov.ua/NBUStatService/v1/statdirectory";

		if ($caching) {
			$entryPointDecoded = json_decode(file_get_contents( __DIR__ . "/cache/statdirectory.cache" ));
		} else {
			$entryPointDecoded = json_decode(file_get_contents( $entryPoint . "/?json" ));
		}

			/* 
			Displaying drop-down menu with list of available datasets. Utilizing:
			- txt (dataset human name)
			- apikod (dataset machine name)

			*/

			echo "<div class=\"form-group\">
				  <label for=\"apikod\">Оберіть групу датасетiв:</label>
				  <select class=\"form-control\" name=\"apikod\">";

			foreach ($entryPointDecoded as $key => $value) {
				echo "<option value=\"" . $value->apikod . "\"";
				if ( isset($_GET["apikod"]) ) { if ($_GET["apikod"] == $value->apikod) { echo " selected"; $dataGroupName = $value->txt; } else { echo " disabled"; } }
				echo ">" . $value->txt . "</option>";			
			}

			echo "</select></label></div>";

			/* 
			Sending a request to clarify available parameters. Utilizing:
			- periods (periodicity, also used as parameter under freq=)
			- dimensions (possible parameters)
			- entrydate (min available date)
			*/

			if (isset($_GET["apikod"]) && !empty($_GET["apikod"])) {

				// If isset apikod, going through every object in $entryPointDecoded

				foreach ($entryPointDecoded as $key => $value) {

					// Looking for the apikod selected in our query
					$apikod = $value->apikod;
					
					if ( $apikod == $_GET["apikod"] ) {

						// Stop searching and write object to a variable
						$datasetDetails = $value;
						// Clear memory
						$entryPointDecoded = null;

						echo "<div class=\"form-group\"><label for=\"freq\">Оберіть інтервал:</label>";

							/* 
							Using periods, dimensions and entrydate.
							Constructing arrays for periods and dimensions
							*/

							// Entrydate format DD.MM.YYYY to be changed only for our form input purposes (min attribute)
							$entrydate = $datasetDetails->entrydate;
							$entrydateMin = date_format(new DateTime($entrydate), 'Y-m-d');

							$dimensions = preg_replace('/\s+/', '', $datasetDetails->dimensions);
							$arrDimensionsExplode = explode(",", $dimensions);
							$arrDimensionsNumCount = count($arrDimensionsExplode);

							$periods = preg_replace('/\s+/', '', $datasetDetails->periods);
							$arrPeriodsExplode = explode(",", $periods);
							$arrPeriodsNumCount = count($arrPeriodsExplode);

						// Implementing periods as freq=

							foreach ($arrPeriodsExplode as $key => $value) {
								echo "
									<div class=\"form-check form-check-inline\">
									  <label class=\"form-check-label\" name=\"freq\">
									    <input class=\"form-check-input\" type=\"radio\" name=\"freq\" value=\"". $value ."\"";								
										// No choice if there is only one period. So it is checked.
										if ($arrPeriodsNumCount <= 1 || isset($_GET["freq"]) && $_GET["freq"] == $value || $value == "M") { echo " checked"; }
								echo "> " . $value . "</label></div>";
							}


						echo "</div>";


						// Implementing dates

							echo "<div class=\"form-group\">
									<label for=\"periodStart\">Початок періода:</label>
									<input type=\"date\" name=\"start\" min=\"" . 
									// using min as entrydate
									$entrydateMin . 
								  "\"";
							if (isset($_GET["start"])) { echo " value=\"" . $_GET["start"] . "\""; };
							echo ">
								  </div>

									<div class=\"form-group\">
									<label for=\"periodEnd\">Кінець періода:</label>
									<input type=\"date\" name=\"end\" max=\"";
							// max attribute is our current date
							echo date('Y-m-d') . "\"";
							if (isset($_GET["end"])) { echo " value=\"" . $_GET["end"] . "\""; };
							echo "></div>";


						// Implementing dimensions

						foreach ($arrDimensionsExplode as $key => $value) {

						// To find human readable titles for dimensions in arrDimensionsExplode
						$urlDimentionDetails = $entryPoint . "/dimension?json";
						// To get list of options in that particular dimension
						$urlDimentionDetailsDeep = $entryPoint . "/dimension/" . $value . "?json";

							/* Check response (headers)
							You can find that some dimensions are unavailable. So we need to check.
							*/

							$urlDimentionDetailsResponse = get_headers($urlDimentionDetails)[0];
							$urlDimentionDetailsDeepResponse = get_headers($urlDimentionDetailsDeep)[0];

							if ($urlDimentionDetailsResponse === "HTTP/1.1 200 OK" && $urlDimentionDetailsDeepResponse === "HTTP/1.1 200 OK" ) {
														
							$jsonDD = json_decode(file_get_contents( $urlDimentionDetails ));

							$jsonDDD = json_decode(file_get_contents( $urlDimentionDetailsDeep ));

							foreach ($jsonDD as $ke => $va) {

								$dimensionkod = $va->dimensionkod;

									if ($dimensionkod == $value) {

										$dimensionDetail = $va;
										
										$jsonDD = null;

										echo "<div class=\"form-group\">";
										echo "<label for=\"" . $dimensionkod . "\">" . $dimensionDetail->txt . "</label>";
										echo "<select class=\"form-control\" 
													  name=\"" . $dimensionkod . "\"
													  id=\"" . $dimensionkod . "\"
													  >";
											
											foreach ($jsonDDD as $k => $v) {
												echo "<option value=\"" . $v->$dimensionkod . "\"";
													if ( isset($_GET["$dimensionkod"]) ) { if ($_GET["$dimensionkod"] == $v->$dimensionkod) { echo " selected"; } }
												echo ">" . $v->txt . "</option>";
											}

										echo "</select>";
										echo "</div>";
									}
								}

								$jsonDDD = null;

							}

						}

						/* 
						We need more properties exactly from the large group of objects 
						in dataset to use as parameters in a final query:
						- txt (human name)
						- id_api (machine name)
						- leveli (level of object)
						- parent (id_api of parent object)
						*/

						// Constructing other parameters taken directly from dataset with test data

						if ($apikod == "liquidity") { //for newly added groups of datasets
							$testDate = "20171101";
						} else {
							$testDate = "20170701";
						}
						
						
						/* 
						start= and end= working normally instead of date=
						so we use them just to get additional params
						*/
						$otherParametersUrl = $entryPoint . "/" . 
											  $apikod . "?start=" . 
											  $testDate . "&end=" . 
											  $testDate ."&json";

						$otherParametersContent = json_decode(file_get_contents( $otherParametersUrl ));

						$dataSetContentModified = array();

							foreach ($otherParametersContent as $key => $value) {

								$leveli = $value->leveli;

								if (isset($leveli)) {
									$dataSetContentModified[$leveli][] = $value;
								}

							}
							
						$dataSetContent = null;

						ksort($dataSetContentModified);


						echo "<div class=\"form-group\">";
						echo "<label for=\"id_api\">Оберіть датасет (параметр): </label>";
					    echo "<select name=\"id_api\" multiple class=\"form-control\" size=\"15\">";

						$count = count($dataSetContentModified);					
						
						foreach ($dataSetContentModified[1] as $key => $value) {
							$idApiParent1 = $value->id_api;

							echo "<option value=\"" . $idApiParent1 . "\"";
							if ( isset($_GET["id_api"]) ) { if ($_GET["id_api"] == $value->id_api) { echo " selected"; } }
							echo ">";
							echo "<b>" . trim($value->txt) . "</b></option>";
							
							foreach ($dataSetContentModified[2] as $key => $value) {

								if ($value->parent == $idApiParent1) {
								echo "<option value=\"" . $value->id_api . "\"";
								if ( isset($_GET["id_api"]) ) { if ($_GET["id_api"] == $value->id_api) { echo " selected"; } }
								echo ">";
								echo "&nbsp;&nbsp;" . trim($value->txt) . "</option>";
								$idApiParent2 = $value->id_api;
								}  else {
									$idApiParent2 = $idApiParent1;
								}

								foreach ($dataSetContentModified[3] as $key => $value) {
									$idApiParent3 = $value->id_api;

									if ($value->parent == $idApiParent2) {
									echo "<option value=\"" . $value->id_api . "\"";
									if ( isset($_GET["id_api"]) ) { if ($_GET["id_api"] == $value->id_api) { echo " selected"; } }
									echo ">";
									echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . trim($value->txt) . "</option>";
									$idApiParent3 = $value->id_api;
									} else {
										$idApiParent3 = $idApiParent2;
									}

									foreach ($dataSetContentModified[4] as $key => $value) {
										$idApiParent4 = $value->id_api;

										if ($value->parent == $idApiParent3) {
										echo "<option value=\"" . $value->id_api . "\"";
										if ( isset($_GET["id_api"]) ) { if ($_GET["id_api"] == $value->id_api) { echo " selected"; } }
										echo ">";
										echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . trim($value->txt) . "</option>";
										$idApiParent4 = $value->id_api;
										} else {
											$idApiParent4 = $idApiParent3;
										}
									} 
								}
							}
						}

						$dataSetContentModified = null;

						echo "</select></div>";

					}
				}

			}

			?>

				<div class="form-group">
					<button type="submit" class="btn btn-success">Обрати</button>
					<a href="/nbu/opendata-explorer" role="button" class="btn btn-warning">Скинути</a>
				</div>

			</form>

		</div>

		<div class="col-md-8">
			<?php

			if (isset($_GET["id_api"])) {

				$arrGet = $_GET;

				// First property is going out to a variable
				$arrShiftedElement = array_shift($arrGet);

				// We got date from user input (form) in inappropriate format, so we need to modify it

				if (isset($arrGet["start"])) {
					$arrGet["start"] = date_format(new DateTime($arrGet["start"]), 'Ymd');
				} if (isset($arrGet["end"])) {
					$arrGet["end"] = date_format(new DateTime($arrGet["end"]), 'Ymd');
				} if (isset($arrGet["date"])) {
					$arrGet["date"] = date_format(new DateTime($arrGet["date"]), 'Ym');
				}

				// Constructing appropriate url
				$urlFull = $entryPoint . "/" . $arrShiftedElement . "?" . 
							   http_build_query($arrGet) . "&json";
				
				$dataSetContent = json_decode(file_get_contents( $urlFull ));

				if (empty($dataSetContent)) {
					echo "<p><b>Інформація відсутня.<br>Спробуйте інші параметри запиту або інтервал.</b></p>";
				} else {

						$arrDataVis = array();
						$minVal = array();	

						// sorting by "dt"
						$sortArray = array();
						$sortArrayFinal = array();

						foreach ($dataSetContent as $key => $value) {

							$sortArray[$value->dt] = $value;
							
						}
			
							ksort($sortArray);

							foreach ($sortArray as $key => $value) {
								$sortArrayFinal[] = $value;
							}

							$sortArray = null;

							$numItemsInSortArrayFinal = count($sortArrayFinal);
							
							if ($numItemsInSortArrayFinal>10) { $labelDisplay = 'rotate'; } else { $labelDisplay = 'wrap'; }

						foreach ($sortArrayFinal as $key => $value) {
							
							$floatMoney = floatval($value->value);
							$money = number_format($floatMoney, 0, ".", "");

							$arrDataVis[] = array(
								"label" => $value->dt,
								"value" => "$money");

							array_push($minVal, number_format($money, 0, ',', ''));
						}

				$arrDataVisOutput = json_encode($arrDataVis, true);

				$min = min($minVal);
		        $max = max($minVal);

		        $minValChart = $min - $min/100;
		        $maxValChart = $max + $max/100;

		        $yAxisMinValue = number_format($minValChart, 0, ',', '');
		        $yAxisMaxValue = number_format($maxValChart, 0, ',', '');

		        // implementing tzep
		        $tzepCode = strtoupper($sortArrayFinal[0]->tzep);
				$urlTzep = $entryPoint . "/dimension/tzep?json";
				$dataTzep = json_decode(file_get_contents($urlTzep));
				foreach ($dataTzep as $key => $value) {
					if ($tzepCode == strtoupper($value->tzep)) {
						$tzepTxt = $value->txt;
						$tzepDef = $value->def;
					}
				}
				
				if ($tzepCode == "T080") {
					$chartType = "column2d";
					$plotColor = "#1E6B7F";
				} else {
					$chartType = "line";
					$plotColor = "#900C3F";
				}
				
				

				$myChart = new FusionCharts($chartType, "myChartContainer", "100%", "90%", "myGreatChartContainer", "json",
		            '{
		                "chart": {
		                    "caption": "' . $dataGroupName . '",
		                    "subCaption": "' . $sortArrayFinal[0]->txt . '",
		                    "xAxisName": "дата",
		                    "yAxisName": "' . $tzepTxt . '",
		                    "numberPrefix": "",
		                    "formatNumber": "0",
		                    "formatNumberScale": "0",
		                    "paletteColors": "' . $plotColor . '",
		                    "divlineColor": "#999999",
        					"divLineDashed": "1",
		                    "bgColor": "#FCFCFC",
		                    "canvasBgAlpha": "0",
		                    "borderAlpha": "20",
		                    "usePlotGradientColor": "0",
		                    "canvasBorderAlpha": "0",
		                    "showBorder": "0",
		                    "showPlotBorder": "0",
		                    "use3DLighting": "0",
		                    "yAxisMinValue": "' . $yAxisMinValue  . '",
                    		"yAxisMaxValue": "' . $yAxisMaxValue  . '",
		                    "showShadow": "0",
		                    "enableSmartLabels": "0",
		                    "startingAngle": "0",
		                    "showPercentValues": "1",
		                    "showPercentInTooltip": "0",
		                    "decimals": "1",
		                    "captionFontSize": "14",
		                    "subcaptionFontSize": "14",
		                    "subcaptionFontBold": "0",
		                    "toolTipColor": "#ffffff",
		                    "toolTipBorderThickness": "0",
		                    "toolTipBgColor": "#000000",
		                    "toolTipBgAlpha": "80",
		                    "toolTipBorderRadius": "2",
		                    "toolTipPadding": "5",
		                    "showHoverEffect": "1",
		                    "showLegend": "1",
		                    "legendBgColor": "#ffffff",
		                    "legendBorderAlpha": "0",
		                    "legendShadow": "0",
		                    "legendItemFontSize": "10",
		                    "legendItemFontColor": "#666666",
		                    "useDataPlotColorForLabels": "1",  
		                	"labelDisplay": "' . $labelDisplay . '",
		                	"slantLabels": "1",
		                    "exportEnabled": "1",
		                    "exportMode": "auto",
		                    "exportShowMenuItem": "1",
		                    "exportFormats": "PNG=PNG|PDF=PDF|XLS=XLS"
		                },
		                "data": ' . $arrDataVisOutput . '
		                }');

		        $myChart->render();


				echo '
					<ul class="nav nav-tabs" id="myTab" role="tablist">
					  <li class="nav-item">
					    <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Графiк</a>
					  </li>
					  <li class="nav-item">
					    <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Таблиця</a>
					  </li>
					  <li class="nav-item">
					    <a class="nav-link" id="messages-tab" data-toggle="tab" href="#messages" role="tab" aria-controls="messages" aria-selected="false">JSON</a>
					  </li>
					</ul>
				';

				echo '
					<div class="tab-content">
				';
				echo '	  
					  <div class="tab-pane active" id="home" role="tabpanel" aria-labelledby="home-tab">
					  <div id="myGreatChartContainer"></div>
					  </div>
				';

				echo '	  
					  <div class="tab-pane" id="profile" role="tabpanel" aria-labelledby="profile-tab">
				';

						echo "<center><h5>" . $dataGroupName . "</h5>";
						echo "<h6>" . $sortArrayFinal[0]->txt . "</h6>";
						echo "<p>" . $tzepTxt . "</p></center>";

						echo "<div class=\"table-modern\"><table class=\"table\">";

						foreach ($sortArrayFinal as $key => $value) {

							echo "<tr>";
								echo "<td>" . $value->dt . "</td>";
								echo "<td>" . number_format($value->value, 0, ',', ' ') . "</td>";
							echo "</tr>";
						}

						echo "</table></div>";
				
				echo '	
					</div>
				';
				echo '	  
					  <div class="tab-pane" id="messages" role="tabpanel" aria-labelledby="messages-tab">
				';

					echo "GET <pre>" . $urlFull . "</pre><br><br>";
					echo "<small>" . json_encode($sortArrayFinal, true) . "</small>";

				echo '	
					</div>
				';
				echo '	
					</div>
				';

		    	} // end if !empty($dataSetContent)

		        $dataSetContent = null;
		        $arrDataVis = null;
		        $arrDataVisOutput = null;
		        $sortArrayFinal = null;
			}

			?>
		

		</div>

<?php require_once("includes/layout/footer.php"); ?>
<!-- Developed by Artem Rumiantsev -->