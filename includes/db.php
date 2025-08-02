
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// Check if we're in a Replit environment and use PostgreSQL
if (getenv('REPLIT_DB_URL') || getenv('PGHOST')) {
    // PostgreSQL connection parameters from environment variables
    $host = getenv('PGHOST');
    $port = getenv('PGPORT');
    $username = getenv('PGUSER');
    $password = getenv('PGPASSWORD');
    $database = getenv('PGDATABASE');

    // Create PostgreSQL connection
    $conn = pg_connect("host=$host port=$port dbname=$database user=$username password=$password");

    // Check connection
    if (!$conn) {
        die("PostgreSQL Connection failed: " . pg_last_error());
    }

    // Create tables if they don't exist
    $tables_sql = file_get_contents(__DIR__ . '/../database/vbind_pg.sql');
    if ($tables_sql) {
        pg_query($conn, $tables_sql);
    }

    // Function to use with PostgreSQL
    if (!function_exists('pg_fetch_all_custom')) {
        function pg_fetch_all_custom($result) {
            $rows = array();
            if ($result) {
                while ($row = pg_fetch_assoc($result)) {
                    $rows[] = $row;
                }
            }
            return $rows; 
        }
    }
} else {
    // MySQL connection parameters (local development)
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "u523895309_vbind_agency";

    // Check if database exists, if not create it
    $temp_conn = mysqli_connect($host, $username, $password);
    if (!$temp_conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Try to create the database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS $database";
    if (mysqli_query($temp_conn, $sql)) {
        // Import database schema if database was just created
        if (mysqli_affected_rows($temp_conn) > 0) {
            // Database was created, import schema
            $sql_file = file_get_contents(__DIR__ . '/../database/vbind_db.sql');
            if ($sql_file) {
                mysqli_select_db($temp_conn, $database);
                mysqli_multi_query($temp_conn, $sql_file);
                
                // Clear results to avoid "Commands out of sync" error
                while (mysqli_next_result($temp_conn)) {
                    if ($result = mysqli_store_result($temp_conn)) {
                        mysqli_free_result($result);
                    }
                }
            }
        }
    }

    mysqli_close($temp_conn);

    // Create database connection
    $conn = mysqli_connect($host, $username, $password, $database);

    // Check connection
    if (!$conn) {
        die("MySQL Connection failed: " . mysqli_connect_error());
    }

    // Set charset to UTF-8
    mysqli_set_charset($conn, "utf8mb4");
}
?>
