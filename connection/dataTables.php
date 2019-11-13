<?php 
require_once(__DIR__ .'/../includes/autoload.php');
                                       
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
$searchValue = $_POST['search']['value']; // Search value
 
$searchQuery = " ";
$i = 0;
if ($_GET['fetch'] == 'releases') {
  if($searchValue != ''){
     $searchQuery = " AND 
      title like '%".$searchValue."%' OR  
      release_id like '%".$searchValue."%' OR  
      p_line like '%".$searchValue."%' OR  
      c_line like '%".$searchValue."%' OR  
      p_genre like '%".$searchValue."%' OR  
      label like '%".$searchValue."%'";
  }

  ## Total number of records without filtering  
  $totalRecords = $framework->dbProcessor("SELECT COUNT(*) AS count FROM new_release", 1)[0]['count'];

  // ## Total number of record with filtering 
  $totalRecordwithFilter = $framework->dbProcessor(sprintf("SELECT COUNT(*) AS count FROM new_release WHERE 1%s", $searchQuery), 1)[0]['count'];
   
  ## Fetch records
  $sql = sprintf("SELECT * FROM new_release WHERE 1%s ORDER BY %s %s LIMIT %s,%s", $searchQuery, $columnName, $columnSortOrder, $row, $rowperpage);

  $empRecords = $framework->dbProcessor($sql, 1);
  $data = array(); 

  if ($empRecords) {
    foreach ($empRecords as $releases) {
      $i++;
      $creator = $databaseCL->userData($releases['by'], 1);
      $set_status = $databaseCL->releaseStatus($releases['release_id']);   
      $upc = $releases['upc'] ? $releases['upc'] : 'N/L';       $copyright = $releases['c_line_year'].' '.$releases['c_line'];
      $copyright = $releases['c_line_year'].' '.$releases['c_line'];
      $recording = $releases['p_line_year'].' '.$releases['p_line'];
      $databaseCL->type = 3;
      $views = $databaseCL->releaseStats($releases['release_id'])[0]; 

      $data[] = array( 
        "id" => $i,
        "title" => $releases['title'],
        "release_id" => $creator['username'],
        "status" => $set_status[0],
        "upc" => $upc,
        "c_line" => $recording,
        "p_line" => $recording,
        "s_genre" => $views['views']
      );
    }
  }
} 
 
// ## Response
$response = array(
  "draw" => intval($draw),
  "iTotalRecords" => $totalRecordwithFilter,
  "iTotalDisplayRecords" => $totalRecords,
  "aaData" => $data
);

echo json_encode($response);
