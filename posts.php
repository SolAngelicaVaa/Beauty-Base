<?php
session_start();
include 'db_config.php';

$threadId = $GET['threadId'] ?? null; 

if(!$threadId){
    echo "Unvalid thread";
    exit();
}

//Hente innlegg 
$sql = "SELECT P.postId, P.content, U.userName, P.created, P.userId
        FROM POSTS P 
        JOIN USERS U ON P.userId = U.userId
        WHERE P.threadId = ? 
        ORDER BY P.created ASC"; 
$stmt = $pdo->prepare($sql);
$stmt->execute([$threadId]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Posts</h2>";

foreach ($posts as $row)
{
    echo "<p><strong>" . $row['userName'] . ":</strong> " . htmlspecialchars($row['content']) . " <em>(" . $row['created'] . ")</em></p>";

    //Hvis brukeren eier innlegget, kan de redigere og slette lenker
    if (isset($_SESSION['userId']) && $_SESSION['userId'] == $row['userId'])
{
    echo " - <a href='edit_post.php?postId=". $row['postId']. "'>Edit</a>";
    echo " | <a href='delete_post.php?postId=". $row['postId']. "'onclick=\'return confirm('Are you sure you want to delete this post?');\">Delete</a>";
}
    echo "</p>";
} 

//Brukeren kan legge til et nytt innlegg hvis de er logget inn

if (isset($_SESSION['userId']))
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        $content = $_POST['content'] ?? '';
        $userId = $_SESSION['userId'];

        $sqlInsert = "INSER INTO POSTS (threadId, userId, content, created) VALUES (?, ?, ?, now())";
        $stmtInsert = $pdo->prepare($sqlInsert);
        $stmtInsert->execute([$threadId, $userId, $content]);

        header("Location: posts.php?threadId=$threadId");
        exit();
    }
    ?>
    <form method="post">
        <textarea name="content" required style="width: 100%; height:100px;" placeholder="Write a post"></textarea><br><br>
        <button type="submit">Add a post</button>
</form>

<?php
} else{
 echo "<p><a href='login.php'>Log in<a/> to write a post.</p>"; 

}
?>

<!-- https://dev.to/puratabla/how-to-create-a-discussion-forum-using-php-and-mysql-33dm?utm_source=chatgpt.com
ttps://www.geeksforgeeks.org/php/what-is-pdo-in-php/ -->