<?php
ini_set('display_errors', 1);
require "vendor/autoload.php";

use Zxing\QrReader;

$qr = new QrReader("input/qr-code.png");
$d_ = json_decode($qr->text(), true);

// print_r($d_);
// exit;
?>

<!DOCTYPE html>

<head>
	<meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
	<title>Web JS - Augmented Reality</title>
	<!-- include three.js library -->
	<script src='js/three.js'></script>
	<!-- include jsartookit -->
	<script src="js/jsartoolkit5/artoolkit.min.js"></script>
	<script src="js/jsartoolkit5/artoolkit.api.js"></script>
	<!-- include threex.artoolkit -->
	<script src="js/threex/threex-artoolkitsource.js"></script>
	<script src="js/threex/threex-artoolkitcontext.js"></script>
	<script src="js/threex/threex-arbasecontrols.js"></script>
	<script src="js/threex/threex-armarkercontrols.js"></script>


</head>

<body style='margin : 0px; overflow: hidden; font-family: Monospace;'>
	<!-- 
  Example created by Lee Stemkoski: https://github.com/stemkoski
  Based on the AR.js library and examples created by Jerome Etienne: https://github.com/jeromeetienne/AR.js/
-->

	<script>
		var scene, camera, renderer, clock, deltaTime, totalTime;

		var arToolkitSource, arToolkitContext;

		var markerRoot1;

		var mesh1, textMesh;

		var text = `<?php echo $d_["text"]; ?>`

		var color = <?php echo "0x" . $d_["color"]; ?>

		var iconPosition = {
			x: <?php echo explode(" ", $d_["icon-position"])[0]; ?>,
			y: <?php echo explode(" ", $d_["icon-position"])[1]; ?>
		};

		var textPosition = {
			x: <?php echo explode(" ", $d_["text-position"])[0]; ?>,
			y: <?php echo explode(" ", $d_["text-position"])[1]; ?>
		};

		initialize();
		animate();

		function initialize() {
			scene = new THREE.Scene();

			let ambientLight = new THREE.AmbientLight(0xcccccc, 0.5);
			scene.add(ambientLight);

			camera = new THREE.Camera();
			scene.add(camera);

			renderer = new THREE.WebGLRenderer({
				antialias: true,
				alpha: true
			});
			renderer.setClearColor(new THREE.Color('lightgrey'), 0)
			renderer.setSize(640, 480);
			renderer.domElement.style.position = 'absolute'
			renderer.domElement.style.top = '0px'
			renderer.domElement.style.left = '0px'
			document.body.appendChild(renderer.domElement);

			clock = new THREE.Clock();
			deltaTime = 0;
			totalTime = 0;

			////////////////////////////////////////////////////////////
			// setup arToolkitSource
			////////////////////////////////////////////////////////////

			arToolkitSource = new THREEx.ArToolkitSource({
				sourceType: 'webcam',
			});

			function onResize() {
				arToolkitSource.onResize()
				arToolkitSource.copySizeTo(renderer.domElement)
				if (arToolkitContext.arController !== null) {
					arToolkitSource.copySizeTo(arToolkitContext.arController.canvas);

					arToolkitContext.arController.addEventListener('getMarker', function(ev) {
						//alert(textMesh.position.z + " --- " + mesh1.position.z);
						//camera.lookAt(textMesh.position)
					});
				}
			}

			arToolkitSource.init(function onReady() {
				onResize()
			});

			// handle resize event
			window.addEventListener('resize', function() {
				onResize()
			});

			////////////////////////////////////////////////////////////
			// setup arToolkitContext
			////////////////////////////////////////////////////////////	

			// create atToolkitContext
			arToolkitContext = new THREEx.ArToolkitContext({
				cameraParametersUrl: 'data/camera_para.dat',
				detectionMode: 'mono'
			});

			// copy projection matrix to camera when initialization complete
			arToolkitContext.init(function onCompleted() {
				camera.projectionMatrix.copy(arToolkitContext.getProjectionMatrix());
			});

			////////////////////////////////////////////////////////////
			// setup markerRoots
			////////////////////////////////////////////////////////////

			// build markerControls
			markerRoot1 = new THREE.Group();
			scene.add(markerRoot1);
			let markerControls1 = new THREEx.ArMarkerControls(arToolkitContext, markerRoot1, {
				type: 'pattern',
				patternUrl: "data/letterA.patt",
			})

			let geometry1 = new THREE.PlaneBufferGeometry(<?php echo $d_["icon-size"] * 1; ?>, <?php echo $d_["icon-size"] * 1; ?>, 4, 4);
			let loader = new THREE.TextureLoader();
			let texture = loader.load("<?php echo $d_["icon-file"]; ?>", render);
			let material1 = new THREE.MeshBasicMaterial({
				map: texture
			});

			mesh1 = new THREE.Mesh(geometry1, material1);
			mesh1.rotation.x = -Math.PI / 2;
			mesh1.position.x = iconPosition.x;
			mesh1.position.y = 0;
			mesh1.position.z = iconPosition.y;
			markerRoot1.add(mesh1);


			var fontLoader = new THREE.FontLoader();
			let font = fontLoader.load('fonts/helvetiker_bold.typeface.json', function(font) {
				var geometry = new THREE.TextBufferGeometry(text, {
					font: font,
					size: 0.5 * <?php echo $d_["text-size"]; ?>,
					height: 0.2 * <?php echo $d_["text-size"]; ?>
				});
				var material = new THREE.MeshBasicMaterial({
					color: color
				});
				textMesh = new THREE.Mesh(geometry, material);
				textMesh.rotation.x = -Math.PI / 2;
				textMesh.position.x = -2 + textPosition.x;
				textMesh.position.y = 1;
				textMesh.position.z = textPosition.y;
				markerRoot1.add(textMesh);
			});

			// Take each item's unique Z offset, and bake it into the geometry.
			//geometry.translate(0, 0, 100);

			// Put the shared x=100,y=100 offset onto the mesh, so it can be changed later.
			//alert(mesh);

		}

		function update() {
			// update artoolkit on every frame
			if (arToolkitSource.ready !== false)
				arToolkitContext.update(arToolkitSource.domElement);
		}


		function render() {
			renderer.render(scene, camera);
		}


		function animate() {
			requestAnimationFrame(animate);
			deltaTime = clock.getDelta();
			totalTime += deltaTime;
			update();
			render();
		}
	</script>

</body>

</html>