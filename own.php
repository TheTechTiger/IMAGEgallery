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
                loadedImages += LoadImage(files[currentIndex])? 1 : 0;
                currentIndex++;
            }
        }
    </script>
</head>

<body>
    <header>
        <input type="text" id="searchInput" placeholder="Search Images">
    </header>

    <div class="gallery" id="imageGallery"></div>

    <div id="imageViewer">

    </div>
    <script>
        // Variables Initlization
        const imageGallery = document.getElementById("imageGallery");
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

        // Initial Load
        for (let index = 0; index < loadBatchSize; index++) {
            LoadImage(files[index]);
        }


        // Functions
        function LoadImage(url) {
            let imageArray = Array.from(document.getElementById('imageGallery').querySelectorAll('img'));
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

    </script>
</body>

</html>