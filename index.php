<?php


//Codeigniter Helper Function
function convertToPdf($sFile,$oFile){
	$CI =& get_instance();
	$CI->db->select('*');
	$libr_dir = $_SERVER['DOCUMENT_ROOT'].'/path to folder where google api php client lib is located';
	$oFile = $_SERVER['DOCUMENT_ROOT'].$oFile;
	require_once($libr_dir.'google-api-php-client/vendor/autoload.php');
	
	putenv('GOOGLE_APPLICATION_CREDENTIALS='.$libr_dir.'path to/client_secret.apps.googleusercontent.com.json');
	$client = new Google_Client();
	$client->setApplicationName('Upload API');
	$client->setAuthConfig($libr_dir.'/path to/auth-cnfig.json');
	$client->setScopes(array('https://www.googleapis.com/auth/drive'));
	$client->useApplicationDefaultCredentials();
	$service = new Google_Service_Drive($client);
	
	$fileMetadata = new Google_Service_Drive_DriveFile(array(
		'name' => $sFile,
		'mimeType' => 'application/vnd.google-apps.document'
	));
	
	try{
		$content = file_get_contents($sFile);
			$file = $service->files->create($fileMetadata, array(
			'data' => $content,
			'uploadType' => 'multipart'
		)
		);
	}catch(Exception $e) {
		return false;
	}
	
	$attempt = 1;
	do{
		//Wait 5000ms
		usleep(500000*$attempt);
		
		//Try to get pdf file.
		$content = $service->files->export($file->getId(), 'application/pdf', array( 'alt' => 'media' ));
		
		//Save just fetched data.
		file_put_contents($oFile, $content->getBody()->getContents());
		
		if(filesize($oFile)) break;
		else $attempt++;
		
	}while(true);
	
	try{
		//deleting file from drive
		$service->files->delete($file->getId());
	}catch(Exception $e) {
		return false;
	}
	return $oFile;
}

//path to source File (to be converted) MS Word, PowerPoint, Excel, Image File
$sourceFile = '/assets/docx/source.docx';
//path to new file to be created or existing to be overwritten, (pdf file)
$newFile = '/assets/pdfs/new.pdf';

convertToPdf($sourceFile,$newFile);

?>