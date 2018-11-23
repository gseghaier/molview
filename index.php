<?php
include_once("php/utility.php");
include_once("php/load.php");
include_once("php/Mobile_Detect.php");

error_reporting(0);

$detect = new Mobile_Detect;
$touch = $detect -> isMobile() || $detect -> isTablet();

if(is_below_IE10())
{
	header('Location: internetExplorer');
	exit;
}

//preserve + sign by encoding it to %2B before parsing it
parse_str(str_replace("+", "%2B", $_SERVER["QUERY_STRING"]));
$metadata = load_metadata($q, $smiles, $cid, $pdbid, $codid);

//layout
$contentClass = "layout-vsplit";
if(isset($layout)) $contentClass = "layout-".$layout;
else if(isset($pdbid)) $contentClass = "layout-model";
?>

<!DOCTYPE html>
<html itemscope itemtype="http://schema.org/Thing">

<!--
This file is part of MolView (http://molview.org)
Copyright (c) 2014, 2015 Herman Bergwerf

MolView is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

MolView is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with MolView.  If not, see <http://www.gnu.org/licenses/>.
-->

<!--
Query parameters:
- q = search query (lookup using CIR)
- smiles = resolve SMILES string
- cid = load CID
- pdbid = load PDBID
- codid = load CIF from COD
- mode = balls || stick || vdw || wireframe || line
- chainType = ribbon || cylinders || btube || ctrace || bonds (alias for chainBonds=bonds)
- chainBonds = true || false
- chainColor = ss || spectrum || chain || residue || polarity || bfactor
- layout = model || sketcher || hsplit || vsplit
- menu = on || off
- dialog = about || help || share || embed
- bg = black || gray || white
-->

	<head>
		<meta charset="UTF-8" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="width=device-width, user-scalable=no" />
		<meta name="mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-capable" content="yes" />

		<link rel="shortcut icon" href="favicon-32x32.png" />
		<?php echo "<title>".$metadata["title"]."</title>"; ?>
		<meta name="author" content="Herman Bergwerf" />
		<meta name="keywords" <?php echo 'content="'.$metadata["keywords"].'"' ?> />

		<!-- Open Graph + Schema.org + Twitter Card -->
		<meta name="twitter:card" content="summary">
		<meta name="twitter:site" content="@molview">
		<meta property="og:type" content="website" />
		<meta property="og:site_name" content="MolView" />
		<?php
			//url
			echo '<meta property="og:url" content="http://'.$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"].'" />';

			//title
			echo '<meta name="twitter:title" property="og:title" content="'.$metadata["title"].'" />';
			echo '<meta itemprop="name" content="'.$metadata["title"].'" />';

			//description
			echo '<meta name="description" content="'.
			$metadata["description"].'" />';
			echo '<meta name="twitter:description" property="og:description" content="'.
			$metadata["description"].'" />';
			echo '<meta itemprop="description" content="'.
			$metadata["description"].'" />';

			//image
			if($metadata["image_url"] != "")
			{
				echo '<meta property="og:image" content="'.$metadata["image_url"].'" />';
				echo '<meta itemprop="image" content="'.$metadata["image_url"].'" />';
				echo '<meta name="twitter:image" content="'.$metadata["image_url"].'" />';
			}

			//special metadata
			echo '<meta itemprop="sameAs" content="'.$metadata["same_as"].'" />';
		?>

		<!-- CSS -->
		<link type="text/css" rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.2.0/css/font-awesome.css" />
		<link type="text/css" rel="stylesheet" href="//fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,700italic,400,300,700" />
		<link type="text/css" rel="stylesheet" href="build/molview-app.min.css" />
		<?php
			if($touch)
			{
				echo '<link id="theme-stylesheet" type="text/css" rel="stylesheet" href="build/molview-touch.min.css" media="screen" />';
			}
			else
			{
				echo '<link id="theme-stylesheet" type="text/css" rel="stylesheet" href="build/molview-desktop.min.css" media="screen" />';
			}
		?>

		<!-- JS -->
		<script type="text/javascript" src="build/molview-base.min.js"></script>
		<script type="text/javascript" src="build/molview-applib.min.js"></script>
		<script type="text/javascript" src="build/molview-datasets.min.js"></script>
		<script type="text/javascript" src="build/molview-core.min.js"></script>
		<script type="text/javascript" src="build/molview-molpad.min.js"></script>
		<script type="text/javascript" src="build/molview-app.min.js"></script>

		<!-- PHP data injection -->
		<script type="text/javascript">
			Model.JSmol.hq = <?php echo ($touch) ? "false" : "true"; ?>;
			MolView.touch = <?php echo ($touch) ? "true" : "false"; ?>;
			MolView.mobile = <?php echo $detect -> isMobile() ? "true" : "false"; ?>;
			MolView.layout = <?php echo '"'.$contentClass.'"'; ?>;

			Request.CIR.available = true;
			Request.HTTP_ACCEPT_LANGUAGE = <?php echo '"'.$_SERVER["HTTP_ACCEPT_LANGUAGE"].'"'; ?>;
			Request.HTTP_CLIENT_IP = <?php
			echo '"';
			if(isset($_SERVER["HTTP_CLIENT_IP"]))
				echo $_SERVER["HTTP_CLIENT_IP"];
			else if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
				echo $_SERVER["HTTP_X_FORWARDED_FOR"];
			else echo $_SERVER["REMOTE_ADDR"];
			echo '"';
			?>;

			if(!Detector.canvas)
			{
				window.location = window.location.origin + window.location.pathname + "htmlCanvas";
			}
		</script>

		<!-- Google Analytics -->
		<script>
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
			ga('create', 'UA-49088779-3', 'molview.org');
			ga('send', 'pageview');
		</script>
	</head>
	<body <?php if(isset($menu)) if($menu == "off") echo 'class="no-menu"'; ?>>
		<svg width="0" height="0">
			<filter id="pubchemImageFilter" height="1" width="1" y="0" x="0" color-interpolation-filters="sRGB">
				<feColorMatrix
					in="SourceGraphic"
					type="matrix"
					values="1.05 0    0    0 0
					        0    1.05 0    0 0
					        0    0    1.05 0 0
					        0    0    0    1 0"></feColorMatrix>
			</filter>
		</svg>
		<div id="progress">
			<canvas id="progress-canvas"></canvas>
		</div>
		<div id="menu">
			<div id="menu-bar" class="hstack">
				<div id="brand"></div>
				<form id="search" class="hstack" action="index.php">
					<div class="input-wrapper">
						<button><i class="fa fa-search"></i></button>
						<input id="search-input" name="q" type="text" autocomplete="off" spellcheck="false" />
						<div class="input-focus"></div>
						<div id="search-dropdown" class="dropdown">
							<a class="dropdown-toggle"></a>
							<ul class="dropdown-menu dropdown-left">
								<li class="menu-item"><a id="action-show-search-layer">Show last search results</a></li>
								<li class="menu-header">Advanced search</li>
								<li class="menu-item"><a id="action-search-pubchem">PubChem Compounds</a></li>
								<li class="menu-item"><a id="action-search-rcsb">RCSB Protein Data Bank</a></li>
								<li class="menu-item"><a id="action-search-cod">Crystallography Open Database</a></li>
							</ul>
						</div>
					</div>
				</form>
				<ul id="main-menu" class="hstack">
					<li id="molview-dropdown" class="dropdown">
						<a class="dropdown-toggle">MolView</a>
						<ul class="dropdown-menu">
							<li class="menu-header">Layout</li>
							<li id="layout-menu">
								<a id="action-layout-model" <?php if($contentClass == "model") echo 'class="selected"' ?>></a>
								<a id="action-layout-hsplit" <?php if($contentClass == "hsplit") echo 'class="selected"' ?>></a>
								<br/>
								<a id="action-layout-vsplit" <?php if($contentClass == "vsplit") echo 'class="selected"' ?>></a>
								<a id="action-layout-sketcher" <?php if($contentClass == "sketcher") echo 'class="selected"' ?>></a>
							</li>
							<li class="menu-header">Theme</li>
							<li class="menu-item"><a id="action-theme-desktop" <?php echo !$touch ? 'class="radio checked"' : 'class="radio"'; ?>>Desktop</a></li>
							<li class="menu-item"><a id="action-theme-touch" <?php echo $touch ? 'class="radio checked"' : 'class="radio"'; ?>>Touch</a></li>
							<li class="menu-header">Information</li>
							<li class="menu-item"><a id="action-help">Help</a></li>
							<li class="menu-item"><a id="action-about">About</a></li>
						</ul>
					</li>
					<li id="tools-dropdown" class="dropdown">
						<a class="dropdown-toggle">Tools</a>
						<ul class="dropdown-menu">
							<li class="menu-header">Link</li>
							<!-- <li class="menu-item"><a id="action-share">Share</a></li> -->
							<li class="menu-item"><a id="action-embed">Embed</a></li>
							<li class="menu-header">Export</li>
							<li class="menu-item"><a id="action-export-sketcher-png">Structural formula image</a></li>
							<li class="menu-item"><a id="action-export-model-png">3D model image</a></li>
							<li class="menu-item"><a id="action-export-model">MOL file</a></li>
							<li class="menu-header">Chemical data</li>
							<li class="menu-item"><a id="action-data-infocard">Information card</a></li>
							<li class="menu-item"><a id="action-data-spectra">Spectroscopy</a></li>
							<li class="menu-item"><a id="model-source" class="disabled" target="_blank">3D model source</a></li>
							<li class="menu-header">Advanced search</li>
							<li class="menu-item"><a id="action-search-similarity">Similarity</a></li>
							<li class="menu-item"><a id="action-search-substructure">Substructure</a></li>
							<li class="menu-item"><a id="action-search-superstructure">Superstructure</a></li>
						</ul>
					</li>
					<li id="model-dropdown" class="dropdown">
						<a class="dropdown-toggle">Model</a>
						<ul class="dropdown-menu">
							<li class="menu-item"><a id="action-model-reset">Reset</a></li>
							<li class="menu-header">Representation</li>
							<li class="menu-item"><a id="action-model-balls" class="r-mode radio checked">Ball and Stick</a></li>
							<li class="menu-item"><a id="action-model-stick" class="r-mode radio">Stick</a></li>
							<li class="menu-item"><a id="action-model-vdw" class="r-mode radio">van der Waals Spheres</a></li>
							<li class="menu-item"><a id="action-model-wireframe" class="r-mode radio">Wireframe</a></li>
							<li class="menu-item"><a id="action-model-line" class="r-mode radio">Line</a></li>
							<li class="menu-header">Background</li>
							<li class="menu-item"><a id="action-model-bg-black" <?php echo 'class="model-bg radio'.(isset($bg) ? $bg == "black" ? ' checked"' : '"' : ' checked"'); ?> >Black</a></li>
							<li class="menu-item"><a id="action-model-bg-gray" <?php echo 'class="model-bg radio'.(isset($bg) ? $bg == "gray" ? ' checked"' : '"' : '"'); ?> >Gray</a></li>
							<li class="menu-item"><a id="action-model-bg-white" <?php echo 'class="model-bg radio'.(isset($bg) ? $bg == "white" ? ' checked"' : '"' : '"'); ?> >White</a></li>
							<li class="menu-header">Engine</li>
							<li class="menu-item"><a id="action-engine-glmol" class="radio checked">GLmol</a></li>
							<li class="menu-item"><a id="action-engine-jmol" class="radio">Jmol</a></li>
							<li class="menu-item"><a id="action-engine-cdw" class="radio">ChemDoodle</a></li>
							<li class="menu-header">Crystallography</li>
							<li class="menu-item"><a id="action-cif-unit-cell">Load unit cell</a></li>
							<li class="menu-item"><a id="action-cif-cubic-supercell">Load 2&times;2&times;2 supercell</a></li>
							<li class="menu-item"><a id="action-cif-flat-supercell">Load 1&times;3&times;3 supercell</a></li>
						</ul>
					</li>
					<li id="protein-dropdown" class="dropdown">
						<a class="dropdown-toggle">Protein</a>
						<ul class="dropdown-menu">
							<li class="menu-item"><a id="action-bio-assembly" class="check">Show bio assembly</a></li>
							<li class="menu-header">Chain representation</li>
							<li class="menu-item"><a id="action-chain-type-ribbon" class="chain-type radio checked">Ribbon</a></li>
							<li class="menu-item"><a id="action-chain-type-cylinders" class="chain-type radio">Cylinder and plate</a></li>
							<li class="menu-item"><a id="action-chain-type-btube" class="chain-type radio">B-factor tube</a></li>
							<li class="menu-item"><a id="action-chain-type-ctrace" class="chain-type radio">C-alpha trace</a></li>
							<li class="menu-divider"></li>
							<li class="menu-item"><a id="action-chain-type-bonds" class="check">Bonds</a></li>
							<li class="menu-header">Chain color scheme</li>
							<li class="menu-item"><a id="action-chain-color-ss" class="chain-color radio checked">Secondary structure</a></li>
							<li class="menu-item"><a id="action-chain-color-spectrum" class="chain-color radio">Spectrum</a></li>
							<li class="menu-item"><a id="action-chain-color-chain" class="chain-color radio">Chain</a></li>
							<li class="menu-item"><a id="action-chain-color-residue" class="chain-color radio">Residue</a></li>
							<li class="menu-item"><a id="action-chain-color-polarity" class="chain-color radio">Polarity</a></li>
							<li class="menu-item"><a id="action-chain-color-bfactor" class="chain-color radio">B-factor</a></li>
						</ul>
					</li>
					<li id="jmol-dropdown" class="dropdown">
						<a class="dropdown-toggle">Jmol</a>
						<ul class="dropdown-menu">
							<li class="menu-item"><a id="action-jmol-hq" class="check">High Quality</a></li>
							<li class="menu-item"><a id="action-jmol-clean" class="jmol-script">Clean</a></li>
							<li class="menu-header jmol-script jmol-calc">Calculations</li>
							<li class="menu-item"><a id="action-jmol-mep-lucent" class="jmol-script jmol-calc">MEP surface lucent</a></li>
							<li class="menu-item"><a id="action-jmol-mep-opaque" class="jmol-script jmol-calc">MEP surface opaque</a></li>
							<li class="menu-item"><a id="action-jmol-charge" class="jmol-script jmol-calc">Charge</a></li>
							<li class="menu-item"><a id="action-jmol-bond-dipoles" class="jmol-script jmol-calc">Bond dipoles</a></li>
							<li class="menu-item"><a id="action-jmol-net-dipole" class="jmol-script jmol-calc">Overall dipole</a></li>
							<li class="menu-item"><a id="action-jmol-minimize" class="jmol-script jmol-calc">Energy minimization</a></li>
							<li class="menu-header jmol-script">Measurement</li>
							<li class="menu-item"><a id="action-jmol-measure-distance" class="jmol-script jmol-picking radio">Distance</a></li>
							<li class="menu-item"><a id="action-jmol-measure-angle" class="jmol-script jmol-picking radio">Angle</a></li>
							<li class="menu-item"><a id="action-jmol-measure-torsion" class="jmol-script jmol-picking radio">Torsion</a></li>
						</ul>
					</li>
				</ul>
			</div>
		</div>
		<div id="content">
			<div id="main-layer" <?php echo 'class="layer '.$contentClass.'"' ?>>
				<!-- Dynamic onload layout -->
				<script type="text/javascript">
					MolView.query = getQuery();

					if(localStorage && localStorage["molview.theme"])
					{
						MolView.setTheme(localStorage["molview.theme"]);
					}

					if($(window).height() > $(window).width()
						&& !MolView.query.layout
						&& MolView.layout != "model") Actions.layout_hsplit();

					//compact menu bar
					MolView.setMenuLayout($(window).width() < 1100,
							$(window).width() < 1100 && !MolView.touch,
							$(window).width() < 390 && MolView.touch);
				</script>
				<div id="sketcher">
					<div id="molpad" class="sketcher">
						<div id="chem-tools" class="toolbar">
							<div class="toolbar-inner">
								<div id="action-mp-bond-single" class="tool-button primary-tool" title="Single bond"></div>
								<div id="action-mp-bond-double" class="tool-button primary-tool" title="Double bond"></div>
								<div id="action-mp-bond-triple" class="tool-button primary-tool" title="Triple bond"></div>
								<div id="action-mp-bond-wedge" class="tool-button primary-tool" title="Wedge bond"></div>
								<div id="action-mp-bond-hash" class="tool-button primary-tool" title="Hash bond"></div>
								<div class="vertical-separator"></div>
								<div id="action-mp-frag-benzene" class="tool-button primary-tool" title="Benzene"></div>
								<div id="action-mp-frag-cyclopropane" class="tool-button primary-tool" title="Cyclopropane"></div>
								<div id="action-mp-frag-cyclobutane" class="tool-button primary-tool" title="Cyclobutane"></div>
								<div id="action-mp-frag-cyclopentane" class="tool-button primary-tool" title="Cyclopentane"></div>
								<div id="action-mp-frag-cyclohexane" class="tool-button primary-tool" title="Cyclohexane"></div>
								<div id="action-mp-frag-cycloheptane" class="tool-button primary-tool" title="Cycloheptane"></div>
								<div class="vertical-separator"></div>
								<div id="action-mp-chain" class="tool-button primary-tool" title="Carbon chain"></div>
								<div id="action-mp-charge-add" class="tool-button primary-tool" title="Charge +">e<sup>+</sup></div>
								<div id="action-mp-charge-sub" class="tool-button primary-tool" title="Charge -">e<sup>&minus;</sup></div>
							</div>
						</div>
						<div id="edit-tools" class="toolbar">
							<div class="toolbar-inner hstack">
								<div id="action-mp-clear" class="tool-button tool-button-horizontal" title="Clear all"></div>
								<div id="action-mp-eraser" class="tool-button tool-button-horizontal primary-tool" title="Erase"></div>
								<div class="horizontal-separator"></div>
								<div id="action-mp-undo" class="tool-button tool-button-horizontal tool-button-disabled" title="Undo"></div>
								<div id="action-mp-redo" class="tool-button tool-button-horizontal tool-button-disabled" title="Redo"></div>
								<div class="horizontal-separator"></div>
								<div id="action-mp-drag" class="tool-button tool-button-horizontal primary-tool" title="Drag atoms and bonds"></div>
								<div id="action-mp-rect" class="tool-button tool-button-horizontal primary-tool" title="Rectangle selection"></div>
								<div id="action-mp-lasso" class="tool-button tool-button-horizontal primary-tool" title="Lasso selection"></div>
								<div class="horizontal-separator"></div>
								<div id="action-mp-color-mode" class="tool-button tool-button-horizontal enabled" title="Toggle color mode"></div>
								<div id="action-mp-skeletal-formula" class="tool-button tool-button-horizontal enabled" title="Toggle skeletal formula"></div>
								<div id="action-mp-center" class="tool-button tool-button-horizontal" title="Center structure"></div>
								<div class="horizontal-separator"></div>
								<div id="action-mp-clean" class="tool-button tool-button-horizontal" title="Clean structure"></div>
								<div id="action-resolve" class="tool-button tool-button-horizontal" title="Update 3D view">2D to 3D</div>
							</div>
						</div>
						<div id="elem-tools" class="toolbar">
							<div class="toolbar-inner">
								<div id="action-mp-atom-c" class="tool-button primary-tool tool-element element-colored" title="Carbon">C</div>
								<div id="action-mp-atom-h" class="tool-button primary-tool tool-element element-colored" title="Hydrogen">H</div>
								<div id="action-mp-atom-n" class="tool-button primary-tool tool-element element-colored" title="Nitrogen">N</div>
								<div id="action-mp-atom-o" class="tool-button primary-tool tool-element element-colored" title="Oxygen">O</div>
								<div id="action-mp-atom-p" class="tool-button primary-tool tool-element element-colored" title="Phosphorus">P</div>
								<div id="action-mp-atom-s" class="tool-button primary-tool tool-element element-colored" title="Sulfur">S</div>
								<div id="action-mp-atom-f" class="tool-button primary-tool tool-element element-colored" title="Fluorine">F</div>
								<div id="action-mp-atom-cl" class="tool-button primary-tool tool-element element-colored" title="Chlorine">Cl</div>
								<div id="action-mp-atom-br" class="tool-button primary-tool tool-element element-colored" title="Bromine">Br</div>
								<div id="action-mp-atom-i" class="tool-button primary-tool tool-element element-colored" title="Iodine">I</div>
								<div id="action-mp-periodictable" class="tool-button primary-tool" title="Periodic Table">...</div>
							</div>
						</div>
						<div id="molpad-canvas-wrapper"></div>
					</div>
				</div>
				<div id="model" <?php
					if(isset($bg))
					{
						echo 'style="background:'.($bg != "white" ? $bg != "gray" ?
							"#000000" : "#cccccc" : "#ffffff").'"';
					}
				?>>
					<!-- Get preferred model background color from localStorage -->
					<script type="text/javascript">
						if(localStorage && localStorage["model.background"])
						{
							var c = localStorage["model.background"];
							$("#model").css("background", c == "gray" ? "#ccc" : c);
						}
					</script>
					<div id="chemdoodle" class="render-engine" style="display: none;">
						<canvas id="chemdoodle-canvas"></canvas>
					</div>
					<div id="jsmol" class="render-engine" style="display: none;"></div>
					<div id="glmol" class="render-engine" style="display: none;"></div>
				</div>
			</div>
			<div id="search-layer" class="layer" style="display: none;">
				<div class="btn-group-bar">
					<button class="btn close btn-primary "><i class="fa fa-arrow-left"></i> Return</button>
				</div>
				<div class="container"></div>
				<div id="action-load-more-pubchem" class="load-more" style="display: none;"></div>
				<div id="action-load-more-rcsb" class="load-more" style="display: none;"></div>
				<div id="action-load-more-cod" class="load-more" style="display: none;"></div>
			</div>
			<div id="infocard-layer" class="layer data-layer" style="display: none;">
				<div class="btn-group-bar">
					<button class="btn close btn-primary "><i class="fa fa-arrow-left"></i> Return</button>
				</div>
				<div id="properties-wrapper">
					<div id="general-properties">
						<div id="molecule-image-wrapper" class="properties-block">
							<img id="molecule-image" alt=""
								style="-webkit-filter: url('#pubchemImageFilter');
										   moz-filter: url('#pubchemImageFilter');
										   -ms-filter: url('#pubchemImageFilter');
										    -o-filter: url('#pubchemImageFilter');
										       filter: url('#pubchemImageFilter');*"/>
						</div>
						<div class="properties-block">
							<div id="molecule-info">
								<h3 id="molecule-title"></h3>
								<p id="molecule-description"></p>
							</div>
							<table id="common-chem-props">
								<tr id="prop-formula-wrapper"><td>Formula</td><td id="prop-formula" class="chemprop"></td></tr>
								<tr id="prop-mw-wrapper"><td>Molecular weight</td><td id="prop-mw" class="chemprop"></td></tr>
								<tr id="prop-donors-wrapper"><td>Hydrogen bond donors</td><td id="prop-donors" class="chemprop"></td></tr>
								<tr id="prop-acceptors-wrapper"><td>Hydrogen bond acceptors</td><td id="prop-acceptors" class="chemprop"></td></tr>
							</table>
							<h3 id="percent-composition-title">Percent composition</h3>
							<table id="percent-composition-table"></table>
						</div>
					</div>
					<div id="prop-sysname-wrapper" class="chem-identifier">
						<label for="prop-sysname">Systematic name</label>
						<input type="text" id="prop-sysname" class="input chemprop" autocomplete="off" spellcheck="false" />
					</div>
					<div id="prop-canonicalsmiles-wrapper" class="chem-identifier">
						<label for="prop-canonicalsmiles">Canonical SMILES</label>
						<input type="text" id="prop-canonicalsmiles" class="input chemprop" autocomplete="off" spellcheck="false" />
					</div>
					<div id="prop-isomericsmiles-wrapper" class="chem-identifier">
						<label for="prop-isomericsmiles">Isomeric SMILES</label>
						<input type="text" id="prop-isomericsmiles" class="input chemprop" autocomplete="off" spellcheck="false" />
					</div>
					<div id="prop-inchikey-wrapper" class="chem-identifier">
						<label for="prop-inchikey">InChIKey</label>
						<input type="text" id="prop-inchikey" class="input chemprop" autocomplete="off" spellcheck="false" />
					</div>
					<div id="prop-inchi-wrapper" class="chem-identifier">
						<label for="prop-inchi">InChI</label>
						<input type="text" id="prop-inchi" class="input chemprop" autocomplete="off" spellcheck="false" />
					</div>
					<div id="prop-cas-wrapper" class="chem-identifier">
						<label for="cas-sysname">CAS Number</label>
						<input type="text" id="prop-cas" class="input chemprop" autocomplete="off" spellcheck="false" />
					</div>
					<div id="prop-csid-wrapper" class="chem-identifier">
						<label for="prop-csid">Chemspider ID
							<a id="chemspider-link" class="link chem-link" target="_blank"><i class="fa fa-external-link"></i></a>
						</label>
						<input type="text" id="prop-csid" class="input chemprop" autocomplete="off" spellcheck="false" />
					</div>
					<div id="prop-cid-wrapper" class="chem-identifier">
						<label for="prop-cid">PubChem Compound ID
							<a id="pubchem-link" class="link chem-link" target="_blank"><i class="fa fa-external-link"></i></a>
						</label>
						<input type="text" id="prop-cid" class="input chemprop" autocomplete="off" spellcheck="false" />
					</div>
				</div>
			</div>
			<div id="spectra-layer" class="layer data-layer" style="display: none;">
				<div class="btn-group-bar">
					<button class="btn close btn-primary "><i class="fa fa-arrow-left"></i> Return</button>
					<select id="spectrum-select"></select>
					<button id="action-export-spectrum-png" class="btn"><i class="fa fa-download"></i> Download PNG image</button>
					<button id="action-export-spectrum-jcamp" class="btn"><i class="fa fa-download"></i> Download JCAMP data</button>
					<a id="spectrum-nist-source" class="btn" target="_blank"><i class="fa fa-external-link"></i> NIST source</a>
				</div>
				<div id="spectrum-wrapper">
					<canvas id="spectrum-canvas"></canvas>
				</div>
			</div>
		</div>
		<div id="messages"></div>
		<div id="autocomplete-dropdown-wrapper" style="display: none;">
			<div id="autocomplete-dropdown"></div>
		</div>
	</body>
</html>
