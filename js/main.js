$.urlParam = function (name) {
	var results = new RegExp("[?&]" + name + "=([^&#]*)").exec(
		window.location.search
	);

	return results !== null ? results[1] || 0 : false;
};
$.initARMode = function () {
	setInterval(function () {
		$.get(
			"input/data.json",
			function (data) {
				console.log(data);
				text = data.text;
				textPosition = {
					x: data["text-position"].split(" ")[0],
					y: data["text-position"].split(" ")[1],
				};
				iconPosition = {
					x: data["icon-position"].split(" ")[0],
					y: data["icon-position"].split(" ")[1],
				};
				iconSize = data["icon-size"];
				textSize = data["text-size"];
				iconFile = data["icon-file"];
				color = new THREE.Color(`#${data.color}`);
			},
			"JSON"
		)
			.fail(function () {})
			.always(function () {
				initializeARScene();
			});
	}, 2000);
	initialize();
	animate();
};
$(function () {
	if (window.location.href.search("ar_js") != -1) {
		$.initARMode();
	} else {
		$("#navigation a").click(function () {
			$("#navigation a").removeClass("active");
			$(this).addClass("active");
			$(".content").hide();
			$(`#${$(this).data("id")}`).show();
		});

		let showQR = $.urlParam("showQR"); //edit
		if (showQR == "true") {
			$("#navigation a").removeClass("active");
			$(".content").hide();
			$(".content:eq(0)").show();
			$("#navigation a:eq(0)").addClass("active");
		} else {
			$(".content:eq(1)").show();
			$("#navigation a:eq(1)").addClass("active").show();
		}
	}
});

var scene, camera, renderer, clock, deltaTime, totalTime;

var arToolkitSource, arToolkitContext;

var markerRoot1;

var mesh1, textMesh;

var text, color, iconPosition, textPosition, iconFile, iconSize, textSize;

/* var text = `<?php echo $d_["text"]; ?>`

var color = <?php echo "0x" . $d_["color"]; ?>

var iconPosition = {
	x: <?php echo explode(" ", $d_["icon-position"])[0]; ?>,
	y: <?php echo explode(" ", $d_["icon-position"])[1]; ?>
};

var textPosition = {
	x: <?php echo explode(" ", $d_["text-position"])[0]; ?>,
	y: <?php echo explode(" ", $d_["text-position"])[1]; ?>
}; */

function initializeARScene() {
	//scene.remove(markerRoot1);
	//scene.add(markerRoot1);

	let geometry1 = new THREE.PlaneBufferGeometry(
		1 * iconSize,
		1 * iconSize,
		4,
		4
	);
	let loader = new THREE.TextureLoader();
	//console.log(iconFile);
	let texture = loader.load(iconFile, render);
	let material1 = new THREE.MeshBasicMaterial({
		map: texture,
	});

	mesh1 = new THREE.Mesh(geometry1, material1);
	mesh1.rotation.x = -Math.PI / 2;
	mesh1.position.x = iconPosition.x;
	mesh1.position.y = 0;
	mesh1.position.z = iconPosition.y;
	markerRoot1.add(mesh1);

	var fontLoader = new THREE.FontLoader();
	let font = fontLoader.load("fonts/helvetiker_bold.typeface.json", function (
		font
	) {
		//console.log(0.2 * textSize);
		//console.log(-2 + parseInt(textPosition.x));
		var geometry = new THREE.TextBufferGeometry(text, {
			font: font,
			size: 0.5 * textSize,
			height: 0.2 * textSize,
		});
		var material = new THREE.MeshBasicMaterial({
			color: color,
		});
		textMesh = new THREE.Mesh(geometry, material);
		textMesh.rotation.x = -Math.PI / 2;
		textMesh.position.x = -2 + parseInt(textPosition.x);
		textMesh.position.y = 1;
		textMesh.position.z = textPosition.y;
		markerRoot1.add(textMesh);
	});
}

function initialize() {
	//alert(123);
	scene = new THREE.Scene();

	let ambientLight = new THREE.AmbientLight(0xcccccc, 0.5);
	scene.add(ambientLight);

	camera = new THREE.Camera();
	scene.add(camera);

	renderer = new THREE.WebGLRenderer({
		antialias: true,
		alpha: true,
	});
	renderer.setClearColor(new THREE.Color("lightgrey"), 0);
	renderer.setSize(640, 480);
	renderer.domElement.style.position = "absolute";
	renderer.domElement.style.top = "0px";
	renderer.domElement.style.left = "0px";
	document.body.appendChild(renderer.domElement);

	clock = new THREE.Clock();
	deltaTime = 0;
	totalTime = 0;

	////////////////////////////////////////////////////////////
	// setup arToolkitSource
	////////////////////////////////////////////////////////////

	arToolkitSource = new THREEx.ArToolkitSource({
		sourceType: "webcam",
	});

	function onResize() {
		arToolkitSource.onResize();
		arToolkitSource.copySizeTo(renderer.domElement);
		if (arToolkitContext.arController !== null) {
			arToolkitSource.copySizeTo(arToolkitContext.arController.canvas);

			arToolkitContext.arController.addEventListener("getMarker", function (
				ev
			) {
				//alert(textMesh.position.z + " --- " + mesh1.position.z);
				//camera.lookAt(textMesh.position)
			});
		}
	}

	arToolkitSource.init(function onReady() {
		onResize();
	});

	// handle resize event
	window.addEventListener("resize", function () {
		onResize();
	});

	////////////////////////////////////////////////////////////
	// setup arToolkitContext
	////////////////////////////////////////////////////////////

	// create atToolkitContext
	arToolkitContext = new THREEx.ArToolkitContext({
		cameraParametersUrl: "data/camera_para.dat",
		detectionMode: "mono",
	});

	// copy projection matrix to camera when initialization complete
	arToolkitContext.init(function onCompleted() {
		camera.projectionMatrix.copy(arToolkitContext.getProjectionMatrix());
	});

	////////////////////////////////////////////////////////////
	// setup markerRoots
	////////////////////////////////////////////////////////////

	markerRoot1 = new THREE.Group();
	scene.add(markerRoot1);

	let markerControls1 = new THREEx.ArMarkerControls(
		arToolkitContext,
		markerRoot1,
		{
			type: "pattern",
			patternUrl: "data/letterA.patt",
		}
	);

	// build markerControls
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
