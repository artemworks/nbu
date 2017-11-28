<?php

						$testDate = "20170701";
						/* 
						start= and end= working normally instead of date=
						so we use them just to get additional params
						*/
						$otherParametersUrl = "https://bank.gov.ua/NBUStatService/v1/statdirectory/basindbank?" . 
											  "start=" . 
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



					    echo "<ul>";

						$count = count($dataSetContentModified);					
						
						foreach ($dataSetContentModified[1] as $key => $value) {
							$idApiParent1 = $value->id_api;

							echo "<li><b>(1) " . $value->txt . "</b></li>";
							
							echo "<ul>";

							foreach ($dataSetContentModified[2] as $key => $value) {
								
								if ($value->parent == $idApiParent1) {
									echo "<li>(2) " . $value->txt . "</li>";
									$idApiParent2 = $value->id_api;
								} else {
									$idApiParent2 = $idApiParent1;
								}

								echo "<ul>";

								foreach ($dataSetContentModified[3] as $key => $value) {
									

									if ($value->parent == $idApiParent2) {
										echo "<li>(3) " . $value->txt . "</li>";
										$idApiParent3 = $value->id_api;
									} else {
										$idApiParent3 = $idApiParent2;
									}

									echo "<ul>";

									foreach ($dataSetContentModified[4] as $key => $value) {
										
										if ($value->parent == $idApiParent3) {
											echo "<li>(4) " . $value->txt . "</li>";
											$idApiParent4 = $value->id_api;
										} else {
											$idApiParent4 = $idApiParent3;
										}
									} 
									echo "</ul>";
								}
								echo "</ul>";
							}
							echo "</ul>";
						}

						echo "</ul>";

?>