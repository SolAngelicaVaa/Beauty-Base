<?php
// SLÅ PÅ FEILRAPPORTING FOR FEILSØKING
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'config.php';
date_default_timezone_set('Europe/Oslo');

// 1. Sjekk at brukeren er logget inn
if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['userId'];

// 2. Sjekk om data er sendt via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: forum.php");
    exit;
}

// Hent og rens inndata
$threadId = isset($_POST['threadId']) ? (int)$_POST['threadId'] : null;
$title = isset($_POST['title']) ? trim($_POST['title']) : null;
$content = isset($_POST['content']) ? trim($_POST['content']) : '';

// -----------------------------------------------------------------
// ENDRING HER: GENERER TIDEN I PHP FØR SQL-SPØRRINGENE
// Dette sikrer at klokkeslettet er korrekt i henhold til 'Europe/Oslo'
// -----------------------------------------------------------------
$current_time = date('Y-m-d H:i:s');


$conn->begin_transaction(); // Start transaksjon for atomisk operasjon

try {
    $target_thread_id = null;
    $is_new_thread = !empty($title) && empty($threadId);

    // --- LOGIKK FOR NY TRÅD VS. SVAR ---

    if ($is_new_thread) {
        // TILFELLE 1: OPPRETTE NY TRÅD (fra new_thread.php)
        if (empty($content)) {
            $_SESSION['error_message'] = "Innhold er påkrevd for å opprette en ny tråd.";
            header("Location: new_thread.php");
            exit;
        }

        // Sett inn ny tråd i THREADS
        // ENDRING: Bruker '?' i stedet for NOW()
        $stmt_thread = $conn->prepare("INSERT INTO THREADS (userId, title, created) VALUES (?, ?, ?)");
        if ($stmt_thread === false) throw new Exception("Feil ved forberedelse av tråd-spørring: " . $conn->error);
        
        // ENDRING: Bindeparameteret har nå 3 variabler: userId (i), title (s), current_time (s)
        $stmt_thread->bind_param("iss", $userId, $title, $current_time); 
        if (!$stmt_thread->execute()) throw new Exception("Feil ved innsetting av tråd: " . $stmt_thread->error);

        $target_thread_id = $conn->insert_id;
        $stmt_thread->close();
        
    } elseif ($threadId > 0) {
        // TILFELLE 2: LEGGE TIL SVAR (fra threads.php)
        if (empty($content)) {
            // Hvis innhold mangler for svar, send tilbake til tråden
            $_SESSION['error_message'] = "Svarinnhold er påkrevd.";
            header("Location: threads.php?id=" . $threadId);
            exit;
        }
        
        $target_thread_id = $threadId; // Bruk eksisterende tråd-ID

    } else {
        // Ugyldig POST-forespørsel
        header("Location: forum.php");
        exit;
    }
    
    // Hvis vi har en gyldig target_thread_id, fortsett med å sette inn innlegget
    if ($target_thread_id > 0) {
        // --- SETT INN INNLEGGET (POSTS) ---
        // ENDRING: Bruker '?' i stedet for NOW()
        $stmt_post = $conn->prepare("INSERT INTO POSTS (threadId, userId, content, created) VALUES (?, ?, ?, ?)");
        if ($stmt_post === false) throw new Exception("Feil ved forberedelse av innlegg-spørring: " . $conn->error);
        
        // ENDRING: Bindeparameteret har nå 4 variabler: threadId (i), userId (i), content (s), current_time (s)
        $stmt_post->bind_param("iiss", $target_thread_id, $userId, $content, $current_time);

        if (!$stmt_post->execute()) throw new Exception("Feil ved innsetting av innlegg: " . $stmt_post->error);
        $stmt_post->close();
        
        // --- FULLFØR OG OMDIRIGER ---
        $conn->commit();
        
        // Suksess: Omdiriger til den riktige trådsiden
        header("Location: threads.php?id=" . $target_thread_id);
        exit;
    }


} catch (Exception $e) {
    $conn->rollback(); // Tilbakerull transaksjonen ved feil
    
    // Logg feilen og vis feilmelding
    error_log("KRITISK FEIL I SAVE_THREAD: " . $e->getMessage()); 
    echo "<h1>Kritisk feil ved publisering</h1>";
    echo "<p>Detaljer: " . $e->getMessage() . "</p>";
    exit;
}
?>