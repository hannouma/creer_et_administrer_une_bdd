<!-- click_tracker.php -->
<?php
require '../vendor/autoload.php'; // This line is included to load MongoDB

// Connect to MongoDB
$client = new MongoDB\Client("mongodb://127.0.0.1:27017");
$db = $client->cinema; // Use your actual database name
$collection = $db->movie_stats; // Collection for movie statistics

// Increment click count for a specific movie.
// $movieId The ID of the movie being clicked.

function incrementClicks($movieId) {
    global $collection;

    // Ensure the movie_id is stored as a string
    $movieId = (int)$movieId;

    // Update the click count for the specified movie ID
    $result = $collection->updateOne(
        ['movie_id' => $movieId], // Filter
        ['$inc' => ['click_count' => 1]], // Increment click count
        ['upsert' => true] // Create the document if it doesn't exist
    );

    return $result->getModifiedCount() > 0 || $result->getUpsertedCount() > 0;
}


// Fetch the click count for a specific movie
// $movieId The ID of the movie being fetched.
function getClickCount($movieId) {
    global $collection;
    
    // Find the document for the movie by movie_id
    $movieStats = $collection->findOne(['movie_id' => $movieId]);

    if ($movieStats) {
        return $movieStats['click_count'] ?? 0; // Return click count or 0 if not set
    } else {
        return 0; // If no record found, return 0 clicks
    }
}

?>
