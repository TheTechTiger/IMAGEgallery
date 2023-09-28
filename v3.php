<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Gallery</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            padding: 20px;
        }

        .gallery img {
            width: 100%;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            transition: transform 0.2s;
            cursor: pointer;
            justify-self: center;
            align-self: center;
        }

        .gallery img:hover {
            transform: scale(1.05);
        }

        #imageViewer {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 9999;
        }

        .closeButton {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 24px;
            color: white;
            cursor: pointer;
        }

        .prev,
        .next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 30px;
            color: white;
            cursor: pointer;
        }

        .prev {
            left: 20px;
        }

        .next {
            right: 20px;
        }

        #displayedImage {
            display: block;
            margin: 0 auto;
            max-width: 90%;
            max-height: 90%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        #searchInput {
            margin: 10px auto;
            padding: 10px;
            width: 100%;
            max-width: 300px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            display: block;
        }

        #searchInput::placeholder {
            color: #999;
        }

        @media (max-width: 600px) {
            .gallery {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 5px;
                padding: 10px;
            }

            #searchInput {
                margin: 5px;
                padding: 5px;
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <header>
        <input type="text" id="searchInput" placeholder="Search Images">
    </header>
    <div class="gallery" id="imageGallery"></div>
    <div id="imageViewer">
        <span class="closeButton" id="closeViewer">&times;</span>
        <img id="displayedImage" />
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const imageGallery = document.getElementById("imageGallery");
            const searchInput = document.getElementById("searchInput");
            const batchSize = 2;
            const loadedImageURLs = [];
            const loadedImagesDueToQuery = [];
            let startIndex = 0;
            let loadingNextBatch = false;
            const loadedImagesForAll = [];
            function loadAllImages() {
                imageGallery.innerHTML = "";
                images.length = 0;
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    if (!loadedImagesForAll.includes(file)) {
                        const img = new Image();
                        img.src = file;
                        img.alt = file;
                        img.style.gridColumn = "span 2";
                        img.style.gridRow = "span 2";
                        img.addEventListener("click", () => {
                            openImageViewer(i);
                        });
                        imageGallery.appendChild(img);
                        images.push(img);
                        loadedImagesForAll.push(file);
                    }
                }
            }
            searchInput.addEventListener("input", function () {
                const query = searchInput.value.toLowerCase();
                if (query === "*") {
                    loadAllImages();
                } else {
                    filterImages(query);
                    loadImagesMatchingQuery(query);
                }
            });
            const files = [
                <?php
                $directory = 'imgs/';
                $files = scandir($directory);
                $files = array_filter($files, function ($file) {
                    return !in_array($file, array('.', '..'));
                });
                foreach ($files as $file) {
                    $filePath = $directory . $file;
                    echo "\"$filePath\",\n";
                }
                ?>
            ];
            const images = [];
            let currentIndex = 0;
            function loadNextBatch() {
                if (startIndex < files.length && !loadingNextBatch) {
                    const endIndex = Math.min(startIndex + batchSize, files.length);
                    loadingNextBatch = true;

                    for (let i = startIndex; i < endIndex; i++) {
                        const file = files[i];
                        if (!loadedImageURLs.includes(file) && !loadedImagesDueToQuery.includes(file)) {
                            const img = new Image();
                            img.src = file;
                            img.alt = file;
                            img.style.gridColumn = "span 2";
                            img.style.gridRow = "span 2";
                            img.addEventListener("click", () => {
                                openImageViewer(i);
                            });
                            imageGallery.appendChild(img);
                            images.push(img);
                            loadedImageURLs.push(file); // Add the loaded URL to the list
                        }
                    }

                    startIndex = endIndex;
                    loadingNextBatch = false;
                }
            }

            loadNextBatch();
            function isAtEndOfPage() {
                const scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
                const windowHeight = window.innerHeight;
                const documentHeight = document.documentElement.scrollHeight;
                return scrollTop + windowHeight >= documentHeight - 100;
            }
            function loadNextBatchOnScroll() {
                if (isAtEndOfPage() && startIndex < files.length && !loadingNextBatch) {
                    loadNextBatch();
                }
            }
            window.addEventListener("scroll", loadNextBatchOnScroll);
            function filterImages(query) {
                imageGallery.innerHTML = "";
                const filteredFiles = files.filter((file) => {
                    const fileName = file.toLowerCase();
                    const keywords = query.split(" ");
                    return keywords.every((keyword) => fileName.includes(keyword));
                });
                images.length = 0;
                currentIndex = 0;
                filteredFiles.forEach((file) => {
                    const img = document.createElement("img");
                    const index = files.indexOf(file);
                    img.src = file;
                    img.alt = file;
                    img.style.gridColumn = "span 2";
                    img.style.gridRow = "span 2";
                    img.addEventListener("click", () => {
                        openImageViewer(index);
                    });
                    imageGallery.appendChild(img);
                    images.push(img);
                });
            }
            function loadImagesMatchingQuery(query) {
                const filteredFiles = files.filter((file) => {
                    const fileName = file.toLowerCase();
                    const keywords = query.split(" ");
                    return keywords.every((keyword) => fileName.includes(keyword));
                });
                filteredFiles.forEach((file) => {
                    if (!loadedImageURLs.includes(file) && !loadedImagesDueToQuery.includes(file)) {
                        const img = new Image();
                        img.src = file;
                        img.alt = file;
                        img.style.gridColumn = "span 2";
                        img.style.gridRow = "span 2";
                        img.addEventListener("click", () => {
                            openImageViewer(files.indexOf(file));
                        });
                        imageGallery.appendChild(img);
                        images.push(img);
                        loadedImagesDueToQuery.push(file);
                    }
                });
            }
            const imageViewer = document.getElementById("imageViewer");
            const displayedImage = document.getElementById("displayedImage");
            const closeViewer = document.getElementById("closeViewer");
            closeViewer.addEventListener("click", () => {
                imageViewer.style.display = "none";
            });
            function openImageViewer(index) {
                currentIndex = index;
                displayImage(currentIndex);
                imageViewer.style.display = "block";
            }
            function displayImage(index) {
                if (index >= 0 && index < images.length) {
                    const selectedImage = images[index];
                    displayedImage.src = selectedImage.src;
                }
            }
            let preloadedImage = null;
            function preloadNextImage() {
                if (currentIndex < images.length - 1) {
                    const nextIndex = currentIndex + 1;
                    const nextImage = images[nextIndex];
                    preloadedImage = new Image();
                    preloadedImage.src = nextImage.src;
                }
            }
            function loadNextImage() {
                if (currentIndex < images.length - 1) {
                    currentIndex++;
                    displayImage(currentIndex);
                } else {
                    loadNextBatch();
                    currentIndex++;
                    displayImage(currentIndex);
                }
            }
            function navigateImage(direction) {
                let newIndex = (currentIndex + direction) % images.length;
                if (newIndex < 0) {
                    newIndex = images.length - 1;
                }
                displayImage(newIndex);
                currentIndex = newIndex;
                preloadNextImage();
                if (currentIndex === images.length - 1 && allImagesLoaded()) {
                    setTimeout(() => {
                        newIndex = 0;
                        displayImage(newIndex);
                        currentIndex = newIndex;
                    }, 500);
                }
            }
            function allImagesLoaded() {
                return images.every((img) => img.complete);
            }
            document.addEventListener("click", (event) => {
                const x = event.clientX;
                const screenWidth = window.innerWidth;
                if (x > screenWidth / 2) {
                    if (currentIndex < images.length - 1) {
                        currentIndex++;
                        displayImage(currentIndex);
                        preloadNextImage();
                    } else {
                        if (allImagesLoaded()) {
                            currentIndex = 0;
                            displayImage(currentIndex);
                        } else {
                            loadNextBatch();
                        }
                    }
                } else {
                    navigateImage(-1);
                }
            });

            document.addEventListener("keydown", function (event) {
                switch (event.key) {
                    case "Escape":
                        imageViewer.style.display = "none";
                        break;
                    case "ArrowLeft":
                        currentIndex = (currentIndex - 1 + images.length) % images.length;
                        displayImage(currentIndex);
                        break;
                    case "ArrowRight":
                        currentIndex = (currentIndex + 1) % images.length;
                        displayImage(currentIndex);
                        break;
                }
            });
        });
    </script>
</body>

</html>