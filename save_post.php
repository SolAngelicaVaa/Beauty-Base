<?php 
session_start();
require 'config.php';

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['userId']))
{
    $threadId = (int)$_POST['threadId'];
    $content = trim($_POST['content']); 

    if($content !== '')
    {
        $userId = $_SESSION['userId'];
        $stmt = $conn->prepare("INSERT INTO POSTS (threadId, userId, content, created) VALUES (?,?,?, NOW())"); 
        $stmt->bind_param("iis", $threadId, $userId, $content);
        $stmt->execute();

        header("Location: threads.php?id=". $threadId);
        exit;
    }else
    {
        echo "Content can't be empty.";
    }
}else
{
    echo "Invalid request.";
}