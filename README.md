# Analytics for EMB
This software processes log data from transaction data at EMB.
## Setup
Create a database using the schema provided in `sql/databaseSchema.sql`. Then, upload the bond definitions and currency definitions found in `bonds.sql` and `currencies.sql`. 

In the second step you need to modify the file in `utils/embx_dbconn.php` where you set some upload and download directories together with the database name, username and password.

```
define("MYSQL_HOST","127.0.0.1");
define("MYSQL_USER","username");
define("MYSQL_PASS","password");
define("MYSQL_DB","databaseName");
define("AUDIO_FILE_PATH","/FullDirectoryPath/downloads/");
define("EMB_SOURCE_FILE_DIRECTORY","/FullDirectoryPath/source_files/");
define("EMB_UPLOAD_DIRECTORY","/FullDirectoryPath/uploads/");
define("TESTISIN","XSTEST123456");
ini_set("upload_tmp_dir","/FullDirectoryPath/uploads/");

```