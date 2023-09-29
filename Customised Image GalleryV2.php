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
    <script>
        function shuffleArray(array) {
            for (let i = array.length - 1; i > 0; i--) {
                let j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
            return array;
        }

        function LoadNextBatch(files, loadBatchSize) {
            var loadedImages = 0, currentIndex = 0;
            while (loadedImages < loadBatchSize && currentIndex < files.length) {
                loadedImages += LoadImage(files[currentIndex]) ? 1 : 0;
                currentIndex++;
            }
        }

        function ProperizeNames(names_ary) {
            let jsonArray = [];
            for (let index = 0; index < names_ary.length; index++) {
                name = names_ary[index].replace(/[^a-zA-Z0-9 ]/g, " ").toLowerCase();
                jsonArray.push({ [names_ary[index]]: name });
            }
            return jsonArray;
        }
    </script>
</head>

<body>
    <header>
        <input type="text" id="searchInput" onChange="filterImages(this.value)" placeholder="Search Images">
    </header>

    <div class="gallery" id="imageGallery"></div>

    <div id="imageViewer">
        <span class="closeButton" onclick="document.getElementById('imageViewer').style.display='none'"
            id="closeViewer">&times;</span>
        <img onclick="ChangeImageViewer(event, true)" id="displayedImage" />
    </div>
    <script>
        // Variables Initlization
        const imageGallery = document.getElementById("imageGallery");
        const displayedImage = document.getElementById("displayedImage");
        const imageViewer = document.getElementById("imageViewer");

        const loadBatchSize = 10;
        var files = [<?php
        $directory = 'imgs/';
        $files = array_filter(scandir($directory), function ($file) {
            return !in_array($file, array('.', '..'));
        });
        foreach ($files as $file) {
            $filePath = $directory . $file;
            echo "\"$filePath\",\n";
        }
        ?>];
        files = shuffleArray(files);
        propernames = ProperizeNames(files);

        // Initial Load
        for (let index = 0; index < loadBatchSize; index++) {
            LoadImage(files[index]);
        }

        // Functions
        function getImagesOnPage() {
            return Array.from(document.getElementById('imageGallery').querySelectorAll('img'));
        }

        // TODO: filterImages
        function filterImages(query) {

        }

        function ChangeImageViewer(event, isimage = false) {
            x = event.pageX;
            if (event.target.tagName.toLowerCase() === 'span') {
                return;
            }
            let width = event.target.offsetWidth;
            let left = event.target.offsetLeft;
            let NEXTimage = 0; //1=next -1=prev
            if (isimage) {
                if (x < (width / 2)) { NEXTimage = -1; }
                else { NEXTimage = 1; }
            }
            else if (x - left < width / 2) {
                NEXTimage = -1;
            } else {
                NEXTimage = 1;
            }

            let imageArray = getImagesOnPage();
            indexOFcurImage = 0;
            imageArray.forEach(function (element, index) {
                if(displayedImage.src == element.src){
                    indexOFcurImage = index;
                }
            });
            indexOFcurImage += NEXTimage;
            if (indexOFcurImage < 0 || indexOFcurImage >= imageArray.length) {
                return;
            }
            else{
                UpdateImageViewer(imageArray[indexOFcurImage].src);
            }
        }
        function UpdateImageViewer(url, show = false) {
            displayedImage.src = url;
            if (show) {
                imageViewer.style.display = "block";
            }
        }

        function LoadImage(url) {
            let imageArray = getImagesOnPage();
            let loadimage = true;

            // Itrating through the image array to check if the image already exists
            for (let index = 0; index < imageArray.length; index++) {
                var ImageElement = imageArray[index];
                if (ImageElement.getAttribute('src') == url) {
                    loadimage = false;
                    break;
                }
            }

            if (loadimage) {
                const img = new Image();
                img.src = url;
                img.alt = url;
                img.style.gridColumn = "span 2";
                img.style.gridRow = "span 2";
                img.addEventListener("click", () => {
                    UpdateImageViewer(url, true);
                });
                imageGallery.appendChild(img);
                return true;
            }
            return false;
        }

        // Event Listners
        window.onscroll = function (ev) {
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight) {
                LoadNextBatch(files, loadBatchSize);
            }
        };

        document.addEventListener("click", function (event) {
            if (imageViewer.style.display != "none" && event.target.id != "displayedImage" && event.target.id == "imageViewer") {
                ChangeImageViewer(event);
            }
        });
    </script>
</body>

</html>