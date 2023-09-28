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

            searchInput.addEventListener("input", function () {
                filterImages(searchInput.value.toLowerCase());
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

            // Create image elements and add them to the gallery
            for (let i = files.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [files[i], files[j]] = [files[j], files[i]];
            }

            // Separate portrait and landscape images
            const portraitImages = [];
            const landscapeImages = [];

            files.forEach((file) => {
                const img = new Image();
                img.src = file;

                // Calculate aspect ratio
                img.onload = function () {
                    const aspectRatio =
                        img.naturalWidth / img.naturalHeight;
                    if (aspectRatio >= 1) {
                        landscapeImages.push(file);
                    } else {
                        portraitImages.push(file);
                    }

                    // Once all images are processed, create the collage
                    if (
                        portraitImages.length + landscapeImages.length ===
                        files.length
                    ) {
                        createCollage();
                    }
                };
            });

            function filterImages(query) {
                // Reset the imageGallery
                imageGallery.innerHTML = "";

                const filteredImages = files.filter((file) => {
                    const fileName = file.toLowerCase();
                    const keywords = query.split(" ");
                    return keywords.every((keyword) => fileName.includes(keyword));
                });

                images.length = 0; // Clear the images array
                currentIndex = 0; // Reset the currentIndex

                // Create and append the filtered images
                filteredImages.forEach((file) => {
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


            function createCollage() {
                // Ensure that a portrait picture is on the left
                let leftImage, rightImages;

                if (portraitImages.length > 0) {
                    leftImage = portraitImages.shift();
                    rightImages = landscapeImages.splice(0, 3); // Take up to 3 landscape images
                } else {
                    leftImage = landscapeImages.shift();
                    rightImages = portraitImages.splice(0, 3); // Take up to 3 portrait images
                }

                // Create and append the left image
                // Create and append the left image
                const leftImg = document.createElement("img");
                const index = files.indexOf(leftImage);
                leftImg.src = leftImage;
                leftImg.alt = leftImage;
                leftImg.style.gridColumn = "span 2";
                leftImg.style.gridRow = "span 2";
                leftImg.addEventListener("click", () => {
                    openImageViewer(index);
                });
                imageGallery.appendChild(leftImg);
                images.push(leftImg); // Add the image to the images array

                // Create and append the right images
                rightImages.forEach((file) => {
                    const rightImg = document.createElement("img");
                    const index = files.indexOf(file);
                    rightImg.src = file;
                    rightImg.alt = file;
                    rightImg.style.gridColumn = "span 1";
                    rightImg.style.gridRow = "span 1";
                    rightImg.addEventListener("click", () => {
                        openImageViewer(index);
                    });
                    imageGallery.appendChild(rightImg);
                    images.push(rightImg); // Add the image to the images array
                });

                // Recursively call createCollage until all images are placed
                if (portraitImages.length + landscapeImages.length > 0) {
                    createCollage();
                }
            }

            const imageViewer = document.getElementById("imageViewer");
            const displayedImage =
                document.getElementById("displayedImage");
            const closeViewer = document.getElementById("closeViewer");
            let currentIndex = 0;

            closeViewer.addEventListener("click", () => {
                imageViewer.style.display = "none";
            });

            // Add an event listener to the displayedImage for clicking/touching the right half
            displayedImage.addEventListener("click", (event) => {
                const x = event.clientX;
                const imageWidth = displayedImage.width;
                if (x > imageWidth / 2) {
                    // Clicked on the right half, navigate to the next image
                    currentIndex = (currentIndex + 1) % images.length;
                    displayImage(currentIndex);
                }
                if (x < imageWidth / 2) {
                    // Clicked on the left half, navigate to the previous image
                    currentIndex = (currentIndex - 1 + images.length) % images.length;
                    displayImage(currentIndex);
                }

            });


            // Add event listeners for key presses
            document.addEventListener("keydown", function (event) {
                switch (event.key) {
                    case "Escape":
                        imageViewer.style.display = "none";
                        break;
                    case "ArrowLeft":
                    case "ArrowUp":
                        navigateImage(-1); // Move to the previous image
                        break;
                    case "ArrowRight":
                    case "ArrowDown":
                        navigateImage(1); // Move to the next image
                        break;
                }
            });

            function openImageViewer(index) {
                currentIndex = index;
                displayImage(currentIndex);
                imageViewer.style.display = "block";
            }

            function displayImage(index) {
                const selectedImage = images[index];
                displayedImage.src = selectedImage.src;
            }

            // Add a function to navigate to the previous or next image
            function navigateImage(direction) {
                currentIndex =
                    (currentIndex + direction + images.length) %
                    images.length;
                displayImage(currentIndex);
            }
        });
    </script>
</body>

</html>