<html>
<head>
<title> Private Schools in Nigeria </title>
<style type="text/css">
@import "styles/util.css";
table
{
	font-family: segoe UI;
	width:80%;
	border-collapse: collapse;
	margin: 10px;
}
td, th
{
	border: 3px solid dimgray;
	text-align: left;
	padding: 8px;
}
th
{
	cursor: pointer;
}
tr:nth-child(even)
{
	background-color: #dddddd;
}

.sorter
{
	background-color: lightblue;
	border: 5px solid dimgray;
	text-align: left;
	padding: 8px;
}

.fonter
{
	font-family: segoe UI;
}
.links
{
	position: absolute;
	margin-left: 25%;
}
.pglink
{
	padding: 5px;
	color:dimgray;
	font-family: segoe UI;
	text-decoration: none;
	transition: 0.4s ease;
}
.pglink:visited
{
	color:dimgray;
}
.pglink:hover
{
	color:white;
	background-color: dimgray;
}
.current
{
	padding: 7px;
	border: 1px solid dimgray;
}
.prevNext
{
	margin: 8px;
	padding: 7px;
	font-size: 18px;
}
.prevNextDiv
{
	margin-left: 25%;
}
.searchBox
{
	height: 40px;
	font-family: segoe UI;
	font-size: 17px;
}
.goButton
{
	background-color: lightgray;
	border-radius: 20px;
	border: 0 solid dimgray;
	font-size:17px;
	padding: 8px;
	width:100px;
	margin-left: 10px;
	cursor:pointer;
	font-weight: 800;
	transition: 0.6s ease;
}
.goButton:hover
{
	background-color: darkred;
	color:white;
	box-shadow: 0px 7px 15px #888888;
	/* In the above, the 3rd parameter is for blur*/
}
.searchDiv
{
	margin-right: 150px;
}
.pglimit
{
	margin-right: 350px;
	font-family: segoe UI;
	max-width: 160px;
	max-height: 30px;
	text-align: center;
	font-size: 17px;
	cursor: pointer;
	background-color: #fff;
	box-shadow: 0px 0px 2px #888888;
}
.pglimit .pgoption
{
	visibility: hidden;
	cursor: pointer;
	width: 160px;
	height: 30px;
	background-color: white;
}
.pglimit:hover .pgoption
{
	visibility: visible;
	
}
.spaceOut
{
	margin: 5px;
}
.error
{
	margin-left: 30%;
}
</style>
</head>
<body>
<br />
<br />
<br />
<?php
if (isset($_GET["pglimit"]))
{
	$pageLimit = filter_var($_GET["pglimit"], FILTER_VALIDATE_INT);
	if ($pageLimit != 5 && $pageLimit != 10 && $pageLimit != 15 && $pageLimit != 20
 	&& $pageLimit != 25 && $pageLimit != 30 && $pageLimit != 35)
		 $pageLimit = 10;
}
else
	$pageLimit = 10;
?>

<div class="searchDiv" align="right">

	<form>
		
		<div class="pglimit"> 10 results per page
			<div class="pgoption" id="pg5" > 5 results per page</div>
			<div class="pgoption" id="pg10" > 10 results per page</div>
			<div class="pgoption" id="pg15" > 15 results per page</div>
			<div class="pgoption" id="pg20" > 20 results per page</div>
			<div class="pgoption" id="pg25" > 25 results per page</div>
			<div class="pgoption" id="pg30" > 30 results per page</div>
			<div class="pgoption" id="pg35" > 35 results per page</div>
		</div>
	
		<input type="text" class="searchBox" name="search" placeholder="Filter results" 
		value="<?php echo (isset($_GET['search']) ? 
		filter_var($_GET['search'], FILTER_SANITIZE_STRING) : '' )?>" />
		<input type="submit" method="post" action = "<?php echo $_SERVER['PHP_SELF']; ?>" 
		class="goButton fonter" name="goButton" value="Go!"/>
	</form>

	
</div>
<br/>


<?php
$fields = array("Serial Number", "Name", "Contact Number", "State");
$fieldNames = array("id", "name", "contactNo", "state");
$tableName = "schools";
$searchString = "";
$sortField = 0;
if (isset($_GET["sort"]))
{
	$sortField = filter_var($_GET["sort"], FILTER_VALIDATE_INT);
	if ($sortField < 0 || $sortField > 7)
		$sortField = 0;
}
$sortStringAppend = " order by " . $fieldNames[$sortField % 4] . ($sortField < 4 ? " asc " : " desc ");
try
{
	$mysqli = new mysqli("localhost", "root", "scramble", "projects");
	if (mysqli_connect_errno())
	{
		// Replace the die argument with a header statement redirecting to an "error page"
		die("Ooops! An error occured while connecting to database: <br />". mysqli_connect_error());
	}
	$filterStringAppend = "";


	if (isset($_POST["search"])) // For initial filter handling
	{
		$buffer = filter_var($_POST["search"], FILTER_SANITIZE_STRING);
		$searchString = "%$buffer%";
		$filterStringAppend = " WHERE name LIKE ? OR state LIKE ?";
	}
	else if (isset($_GET["search"])) // For browsing the filtered results
	{
		$buffer = filter_var($_GET["search"], FILTER_SANITIZE_STRING);
		$searchString = "%$buffer%";
		$filterStringAppend = " WHERE name LIKE ? OR state LIKE ?";
	}

	// Below, number of rows returned for query result is determined either for 
	// default query or filter query
	if ($filterStringAppend)
	{
		$stmt = $mysqli->prepare("SELECT COUNT(*) FROM $tableName " . $filterStringAppend);
		//|| die("Ooops! Error occured: <br/>" . $mysqli->error);  // Why does this not work??
		$stmt->bind_param("ss", $searchString, $searchString);
		$stmt->execute();
		$stmt = $stmt->get_result();
		if ($mysqli->error)
			die("Query failed: <br />" . $mysqli->error);
	}
	else
	{
		$stmt = $mysqli->query("SELECT COUNT(*) FROM $tableName ");
		if ($mysqli->error)
			die("Query failed: <br />" . $mysqli->error);
	}

	// Number of pages for displayed results is determined and set to integer (rethink)
	$totalRowCount = $stmt->fetch_row()[0];
	if ($totalRowCount < 1)
	{
		echo "<span class='error fonter'> No results found </span>";
		die();
	}
	$totalPages = ($totalRowCount / $pageLimit) + ($totalRowCount % $pageLimit == 0 ? 0 : 1);
	settype(($totalPages), "int");

	// Read current page number from url unless the user just used the filter, in which case,
	// start from beginning
	if (!isset($_POST["search"]) && isset($_GET["pgnumber"]))
	{
		$currentPage = filter_var($_GET["pgnumber"], FILTER_VALIDATE_INT);
		if (!is_int($currentPage) || $currentPage > $totalPages)
			$currentPage = 1;
	}
	else
		$currentPage = 1;


	$stmt = $mysqli->prepare("SELECT * FROM $tableName " . $filterStringAppend 
		. $sortStringAppend . " limit ?, ?");

	$startingRow = ($currentPage - 1) * $pageLimit;
	if ($filterStringAppend)
	{
		$stmt->bind_param("ssdd",  $searchString, $searchString, 
			$startingRow, $pageLimit);

	}
	else
		$stmt->bind_param("dd", $startingRow, $pageLimit);
	$stmt->execute();
	if ($mysqli->error)
		die("Ooops! Error occured: <br/>" . $mysqli->error);
	$stmt->bind_result($id, $name, $contactNo, $state); // This must be equivalent to the $fields array
}
catch (Exception $e)
{
	die("Ooops! An error occured while connecting to the database");
}
?>
<table>
	<tr>
		<?php
		foreach($fields as $index => $field)
			echo "<th id=\"" . $fieldNames[$index] . "\""
			. ($index == $sortField || ($sortField - $index == 4) ? 
				" class='sorter" : " class='") . 
			" tooltip'> <span class='tooltiptext'> Sort by {$fields[$index]} </span>$field</th>";
		?>
	</tr>
	<?php
	while ($stmt->fetch())
	{
		$results = array($id, $name, $contactNo, $state);
		echo "<tr>";
		foreach ($results as $value) 
		{
			echo "<td> $value </td>";
		}
		echo "</tr>";
	}
	?>
</table>
<br/>
<br/>
<div class="links">
	<?php
	$startPageLink = (($currentPage >= 5 && $totalPages > 10) ? 
		((($totalPages - $currentPage >= 5) ? $currentPage - 4 : $totalPages - 9)) : 1);
	$endPageLink = (($totalPages <= $startPageLink + 9) ? $totalPages : $startPageLink + 9);
	for ($i = $startPageLink; $i <= $endPageLink; $i++)
	{
		echo "<span" .($i != $currentPage ? ">" : " class=\"current\">");
		echo ($i != $currentPage ? ("<a href=\"" . $_SERVER["PHP_SELF"] . "?pgnumber=" . $i
			. "&pglimit=" . $pageLimit . "&search=" . str_replace("%", "", $searchString)
			 . "&sort=" . $sortField . "\" class=\"pglink\"> $i </a>") : "$i" );
		echo "</span>";
	}
	echo ($totalPages > 10 ? "  <span class=\"fonter spaceOut\">   ($totalPages pages) </span>" : "");
	?>
	<br />
	<br/>
	<div class="prevNextDiv">
		<span class="prevNext">
			<?php echo ("<a href=\"" . $_SERVER["PHP_SELF"] . "?pgnumber=1&pglimit=" 
			. $pageLimit . "&search=" . str_replace("%", "", $searchString) .
			"&sort=" . $sortField . "\" class=\"pglink\"> << </a>");?>
		</span>
		<span class="prevNext">
			<?php
				$prevPageLink = $currentPage > 1 ? $currentPage - 1 : 1;
				echo ("<a href=\"" . $_SERVER["PHP_SELF"] . "?pgnumber=". $prevPageLink
			 	."&pglimit=" . $pageLimit . "&search=" . str_replace("%", "", $searchString) .
			 	"&sort=" . $sortField . "\" class=\"pglink\"> < </a>");
			 ?>
		</span>
		<span class="prevNext">
			<?php
				$nextPageLink = $totalPages > $currentPage ? $currentPage + 1 : $totalPages;
			 	echo ("<a href=\"" . $_SERVER["PHP_SELF"] . "?pgnumber=". ($nextPageLink)
			 	."&pglimit=" . $pageLimit . "&search=" . str_replace("%", "", $searchString) .
			  	"&sort=" . $sortField . "\" class=\"pglink\"> > </a>");
			 ?>
		</span>
		<span class="prevNext">
		<?php
			echo ("<a href=\"" . $_SERVER["PHP_SELF"] . "?pgnumber=". ($totalPages)
		 	."&pglimit=" . $pageLimit . "&search=" . str_replace("%", "", $searchString) .
			  "&sort=" . $sortField . "\" class=\"pglink\"> >> </a>");
		 ?>
		</span>
	</div>
</div>

<br/>
<br/>
<br/>
<br/>
<br/>
<br />
<br />



</body>
</html>
<script src="scripts/util.js"></script>
<script type="text/javascript">
	window.onload = function ()
	{
		document.getElementById("name").onclick = function(e)
		{
			var sortField = <?php echo json_encode($sortField) ?>;
			location.href = <?php echo json_encode($_SERVER['PHP_SELF']) ?>
			+ insertUrlAttribute(location.search, "sort", 
				(sortField == 1 ? 5 : 1));

		}
		document.getElementById("id").onclick = function()
		{
			var sortField = <?php echo json_encode($sortField) ?>;
			location.href = <?php echo json_encode($_SERVER['PHP_SELF']) ?>
			+ insertUrlAttribute(location.search, "sort", 
				(sortField == 0 ? 4 : 0));
		}
		document.getElementById("contactNo").onclick = function()
		{
			var sortField = <?php echo json_encode($sortField) ?>;
			location.href = <?php echo json_encode($_SERVER['PHP_SELF']) ?>
			+ insertUrlAttribute(location.search, "sort", 
				(sortField == 2 ? 6 : 2));
		}
		document.getElementById("state").onclick = function()
		{
			var sortField = <?php echo json_encode($sortField) ?>;
			location.href = <?php echo json_encode($_SERVER['PHP_SELF']) ?>
			+ insertUrlAttribute(location.search, "sort", 
				(sortField == 3 ? 7 : 3));
		}
	}
</script>