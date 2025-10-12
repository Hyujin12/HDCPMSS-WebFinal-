<?php
$services = [
    [
        "title" => "Dental Checkup",
        "image" => "/images/dental-check-up.jpg",
        "description" => "A dental checkup is a routine examination that helps identify any potential dental issues early on. It typically includes a thorough cleaning, examination, and sometimes X-rays to ensure your teeth and gums are healthy.",
        "btn" => "Book Now"
    ],
    [
        "title" => "Dental Extraction",
        "image" => "/images/dental-extraction.jpg",
        "description" => "Dental extraction is a surgical procedure to remove a tooth from the mouth. Dentists perform dental extractions for a variety of reasons, such as tooth decay, gum disease, or overcrowding.",
        "btn" => "Book Now"
    ],
    [
        "title" => "Dental Restoration",
        "image" => "/images/dental-restoration.jpg",
        "description" => "Dental restoration is a process of restoring a tooth to its original shape, function, and appearance using composite resin, porcelain, or gold.",
        "btn" => "Book Now"
    ],
    [
        "title" => "Dental Surgery",
        "image" => "/images/dental-surgery.jpg",
        "description" => "Dental surgery involves procedures like extractions, gum surgeries, and jaw corrections. These surgeries are performed by oral surgeons.",
        "btn" => "Book Now"
    ],
    [
        "title" => "Oral Prophylaxis",
        "image" => "/images/oral-prophylaxis.jpg",
        "description" => "Oral prophylaxis is a preventive dental cleaning to remove plaque, tartar, and stains. Recommended every 6 months.",
        "btn" => "Book Now"
    ],
    [
        "title" => "Orthodontics",
        "image" => "/images/orthodontics.jpg",
        "description" => "Orthodontics involves braces or aligners to straighten teeth and fix bite issues, improving function and aesthetics.",
        "btn" => "Book Now"
    ],
    [
        "title" => "Prosthodontics",
        "image" => "/images/prosthodontics.jpg",
        "description" => "Prosthodontics focuses on replacing missing teeth with crowns, bridges, dentures, or implants.",
        "btn" => "Book Now"
    ]
];

function renderServices($services, $containerId) {
    echo '<div id="'.htmlspecialchars($containerId).'">';
    foreach ($services as $service) {
        echo '<div class="service-card">';
        echo '<img src="'.htmlspecialchars($service['image']).'" alt="'.htmlspecialchars($service['title']).'">';
        echo '<h2>'.htmlspecialchars($service['title']).'</h2>';
        echo '<p>'.htmlspecialchars($service['description']).'</p>';
        echo '<button>'.htmlspecialchars($service['btn']).'</button>';
        echo '</div>';
    }
    echo '</div>';
}
?>
