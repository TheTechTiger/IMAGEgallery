<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Image Gallery</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill,
                    minmax(200px, 1fr));
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
            const batchSize = 50; // Number of images to load per batch
            const loadedImageURLs = []; // Maintain a list of loaded image URLs
            const loadedImagesDueToQuery = []; // Maintain a list of images loaded due to search queries
            let startIndex = 0; // Starting index for loading images
            let loadingNextBatch = false;

            // Create a list to maintain loaded image URLs for the "load all" scenario
            const loadedImagesForAll = [];

            // Function to load all images
            function loadAllImages() {
                // Clear the current images in the gallery
                imageGallery.innerHTML = "";
                images.length = 0;

                // Load only the images that are not already loaded
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];

                    // Check if the image URL is not in the loadedImagesForAll list
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
                        loadedImagesForAll.push(file); // Add the loaded URL to the list
                    }
                }
            }

            // Modify the search input event listener to call loadAllImages when query is "*"
            searchInput.addEventListener("input", function () {
                const query = searchInput.value.toLowerCase();

                if (query === "*") {
                    // If the query is "*", load all images
                    loadAllImages();
                } else {
                    // Otherwise, filter and load images matching the query
                    filterImages(query);
                    loadImagesMatchingQuery(query);
                }
            });
            const files = [
                <?php
                $directory = 'imgs/'; // Specify the directory path
                $files = scandir($directory); // Get a list of files in the directory
                
                // Filter out "." and ".." from the list
                $files = array_filter($files, function ($file) {
                    return !in_array($file, array('.', '..'));
                });

                // Print the complete path of each file
                foreach ($files as $file) {
                    $filePath = $directory . $file;
                    echo "\"$filePath\",\n";
                }
                ?>
            ];
            const images = [];
            let currentIndex = 0; // Track the current image being displayed

            // Create image elements and add them to the gallery
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

            // Initial load of the first batch of images
            loadNextBatch();

            function isAtEndOfPage() {
                const scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
                const windowHeight = window.innerHeight;
                const documentHeight = document.documentElement.scrollHeight;

                return scrollTop + windowHeight >= documentHeight - 100; // Adjust this threshold as needed
            }

            function loadNextBatchOnScroll() {
                if (isAtEndOfPage() && startIndex < files.length && !loadingNextBatch) {
                    loadNextBatch();
                }
            }

            // Add the scroll event listener to trigger loading the next batch
            window.addEventListener("scroll", loadNextBatchOnScroll);

            // Function to filter and display images based on the search query
            function filterImages(query) {
                // Reset the imageGallery
                imageGallery.innerHTML = "";

                const filteredFiles = files.filter((file) => {
                    const fileName = file.toLowerCase();
                    const keywords = query.split(" ");
                    return keywords.every((keyword) => fileName.includes(keyword));
                });

                images.length = 0; // Clear the images array
                currentIndex = 0; // Reset the currentIndex

                // Create and append the filtered images
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
                    images.push(img); // Add the image to the images array
                });
            }

            // Function to load images matching the search query
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
                        loadedImagesDueToQuery.push(file); // Add the loaded URL to the list
                    }
                });
            }

            const imageViewer = document.getElementById("imageViewer");
            const displayedImage = document.getElementById("displayedImage");
            const closeViewer = document.getElementById("closeViewer");

            closeViewer.addEventListener("click", () => {
                imageViewer.style.display = "none";
            });

            // Function to open the image viewer and display a specific image
            function openImageViewer(index) {
                currentIndex = index;
                displayImage(currentIndex);
                imageViewer.style.display = "block";
            }

            // Function to display a specific image in the image viewer
            function displayImage(index) {
                if (index >= 0 && index < images.length) {
                    const selectedImage = images[index];
                    displayedImage.src = selectedImage.src;
                }
            }

            let preloadedImage = null;

            // Function to preload the next image
            function preloadNextImage() {
                // Check if there's a next image to preload
                if (currentIndex < images.length - 1) {
                    const nextIndex = currentIndex + 1;
                    const nextImage = images[nextIndex];
                    preloadedImage = new Image();
                    preloadedImage.src = nextImage.src;
                }
            }

            // Add event listeners for navigating images within the viewer
            function loadNextImage() {
                // Check if there's a next image to load
                if (currentIndex < images.length - 1) {
                    currentIndex++;
                    displayImage(currentIndex);
                } else {
                    // If there are no more images, you can load the next batch here
                    loadNextBatch();
                }
            }
            // Function to navigate to the previous or next image
            // Function to navigate to the previous or next image
            function navigateImage(direction) {
                let newIndex = (currentIndex + direction) % images.length;
                if (newIndex < 0) {
                    newIndex = images.length - 1; // Wrap around to the last image if at the beginning
                }
                displayImage(newIndex);
                currentIndex = newIndex;
                preloadNextImage(); // Preload the next image

                // Check if on the last image and all images are loaded
                if (currentIndex === images.length - 1 && allImagesLoaded()) {
                    setTimeout(() => {
                        newIndex = 0; // Navigate to the first image
                        displayImage(newIndex);
                        currentIndex = newIndex;
                    }, 500); // Delay the navigation to ensure the first image is displayed
                }
            }

            // Function to check if all images are loaded
            function allImagesLoaded() {
                return images.every((img) => img.complete);
            }


            // Add an event listener to the displayedImage for clicking/touching the right half
            document.addEventListener("click", (event) => {
                const x = event.clientX;
                const screenWidth = window.innerWidth;

                if (x > screenWidth / 2) {
                    // Clicked on the right half, navigate to the next image
                    if (currentIndex < images.length - 1) {
                        currentIndex++;
                        displayImage(currentIndex);
                        preloadNextImage(); // Preload the next image
                    } else {
                        // If there are no more images, you can load the next batch here
                        loadNextBatch();
                        currentIndex++;
                        displayImage(currentIndex);
                    }
                } else {
                    navigateImage(-1); // Move to the previous image
                }
            });


            // Add event listener for keyboard navigation
            document.addEventListener("keydown", function (event) {
                switch (event.key) {
                    case "Escape":
                        imageViewer.style.display = "none";
                        break;
                    case "ArrowLeft":
                        // Navigate to the previous image
                        currentIndex = (currentIndex - 1 + images.length) % images.length;
                        displayImage(currentIndex);
                        break;
                    case "ArrowRight":
                        // Navigate to the next image
                        currentIndex = (currentIndex + 1) % images.length;
                        displayImage(currentIndex);
                        break;
                }
            });
        });
    </script>
</body>

</html>