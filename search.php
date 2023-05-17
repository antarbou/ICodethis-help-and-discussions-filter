<?php
ob_start();
include_once('layout.main.php');
include('auth_session_admin.php');
include_once('pagination.class.php'); 

if ($_SESSION['isAdmin'] != 1 and $_SESSION['isAdmin'] != 2) {
  header('location: ../index.php');
}
ob_end_flush();
$baseURL = 'getDataSearch.php'; 
$limit = 10; 

$usersession = $_SESSION['citoyen'];
$iduser = $_SESSION['id'];
$create_datetime = date("Y-m-d H:i:s");
$result = [];
$errors = [];

  $query_sql = "SELECT  COUNT(*) As rowNum FROM `inchighalat`
            JOIN daira ON daira.coddaira = inchighalat.daira
            JOIN commune ON commune.codcom = inchighalat.commune
            JOIN users ON users.id = inchighalat.iduser
            LEFT JOIN village ON village.idvillage = inchighalat.village ";

$query   = $con->query($query_sql); 
$result  = $query->fetch_assoc(); 
$rowCount= $result['rowNum']; 

$pagConfig = array( 
    'baseURL' => $baseURL, 
    'totalRows' => $rowCount, 
    'perPage' => $limit, 
    'contentDiv' => 'datafilter', 
    'link_func' => 'searchFilter' 
); 
$pagination =  new Pagination($pagConfig); 

// bring data daira 
//----- end setup pagination
$query_all = "SELECT daira.libdairaar,commune.libcomar,village.libvillagear,`adresse`,`secteur`,`typeinchighal`,
            `typeuser`,`object`,`dateadd`,`alreadyopen`,inchighalat.`daira`,inchighalat.`commune` ,inchighalat.`id` 
            FROM `inchighalat`
            JOIN daira ON daira.coddaira = inchighalat.daira
            JOIN commune ON commune.codcom = inchighalat.commune
            JOIN users ON users.id = inchighalat.iduser
            LEFT JOIN village ON village.idvillage = inchighalat.village LIMIT $limit";

  $query_run = mysqli_query($con, $query_all);
  $result = [];
  while ($row = $query_run->fetch_row()) {
    $result[] = $row;
  }
?>
<div class="pt-4 bg-primaryAltDarkest shadow-lg min-h-screen">
  <div class="flex flex-row justify-between">
    <div class="flex items-center p-4  mx-5">
      <a href="dashboard.php" type="button"
        class="text-blue-700 border border-white hover:bg-blue-700 hover:text-white focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-full text-sm p-2.5 text-center inline-flex items-center ">
        <svg aria-hidden="true" class="w-5 h-5" fill="white" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd"
            d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
            clip-rule="evenodd"></path>
        </svg>
        <span class="sr-only">العودة </span>
      </a>
      <div class="mr-2 text-white font-semibold">
        عودة
      </div>
    </div>
    <div class="p-4 mx-5">
      <div class="text-grayLighter font-semibold">
        <?php
        if (isset($_SESSION['citoyen'])) {
          echo $_SESSION['citoyen'];
        }
        ?>
      </div>
    </div>
  </div>
  <hr class="mb-4 border-white sm:mx-auto" />
  <div class="flex flex-col justify-between items-center">
  <div class="flex flex-col justify-between items-center">
      <!--Message error-->
      <?php
      if (isset($errors) && count($errors) <> 0) {
        ?>
        <div class="mb4">
          <div id="alert-border-2" class="flex p-4 mb-4 bg-red-100 border-t-4 border-red-500 dark:bg-red-200"
            role="alert">
            <svg class="flex-shrink-0 w-5 h-5 text-red-700" fill="currentColor" viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd"
                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                clip-rule="evenodd"></path>
            </svg>
            <div class="mr-3 text-sm font-medium grow text-red-700">
              <?php
          
                foreach ($errors as $error) {
                  echo $error, ' test<br>';
                }
       
             ?>
            </div>
            <button onclick="closeAlert(event)" type="button"
              class="mr-auto -mx-1.5 -my-1.5 bg-red-100 dark:bg-red-200 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 dark:hover:bg-red-300 inline-flex h-8 w-8"
              data-dismiss-target="#alert-border-2" aria-label="Close">
              <span class="sr-only">Dismiss</span>
              <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                  d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                  clip-rule="evenodd"></path>
              </svg>
            </button>
          </div>
        </div>
        <?php } ?>
      <!-- end of div error-->
    </div>

  </div>

  <div class="flex flex-col sm:flex-row  mx-auto gap-5">

    <div id="contents" class="flex flex-grow  flex-col text-grayLighter items-right px-4 py-6  md:p-12 md:mx-0">
      <!---->
      <div id="inchighalat" class="flex flex-col justify-between items-center overflow-hidden ">

        <!-- search by fields -->
        <div class="mb-4  w-full md:w-08/12 ">
          <form action="#"  method="POST" onsubmit="event.preventDefault(); searchFilter();">
            <div class="flex flex-col sm:flex-row justify-center gap-5">
              <div class="div">
              <input type="text" id="id" name="id"  onkeyup="searchFilter();"
                class=" w-full p-2  text-sm text-gray border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"
                placeholder="رقم الإنشغال" />
               
              </div>
              <p class="flex justify-center items-center">أو</p>
              <div class="flex flex-row gap-6">
                   <input type="search" id="surename" name="surename" onkeyup="changer_lettre('surename');searchFilter();"
                class=" w-full p-2  text-sm text-gray border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"
                placeholder="اللقب" value=""/>
              <input type="search" id="name" name="name" onkeyup="changer_lettre('name');searchFilter();"
                class=" w-full p-2 text-sm text-gray border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"
                placeholder="الإسم"  value="" />
              <input type="date" id="daten" name="daten"
                class=" w-full p-2 text-sm text-gray border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"
                value="" />
              </div>
           
              <button type="submit" name="search" 
                class="text-white left-2.5 bottom-2.5 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 ">بحث</button>
            </div>
          </form>
        </div>
        <script>
            function searchFilter(page_num) {
                page_num = page_num ? page_num : 0;
                var surename = $('#surename').val();
                var name = $('#name').val();
                var daten = $('#daten').val();
                var id = $('#id').val();
                $.ajax({
                    type: 'POST',
                    url: 'scripts/getDataSearch.php',
                    data: 'page=' + page_num +'&surename=' + surename + '&name=' + name + '&daten=' + daten + '&id=' + id,
                    beforeSend: function () {
                        $('.loading-overlay').show();
                    },
                    success: function (html) {
                        $('#dataContainer').html(html);
                        $('.loading-overlay').fadeOut("slow");
                    }
                });
            }
         </script> 
        <?php
        if (count($result) > 0) {
          ?>
          <!--end panel search -->
          <div id="dataContainer" class="w-full flex flex-col overflow-x-auto ">
            <table
              class="table-auto w-full text-sm mt-4 text-right text-gray-500 rounded-lg bg-primaryDarkest">
              <thead class="text-sm text-gray-700 m-2">
                <tr>
                  <th scope="col" class="py-3 px-4">
                    رقم الإنشغال
                  </th>
                  <th scope="col" class="py-3 px-4">
                    الدائرة
                  </th>
                  <th scope="col" class="py-3 px-4">
                    البلدية
                  </th>
                  <th scope="col" class="py-3 px-4">
                    مكان الإنشغال
                  </th>
                  <th scope="col" class="py-3 px-4">
                    القطاع
                  </th>
                  <th scope="col" class="py-3 px-4">
                    مجال المنفعة والاستفادة
                  </th>
                  <th scope="col" class="py-3 px-4">
                    صفة المحرر
                  </th>
                  <th scope="col" class="py-3 px-4">
                    موضوع الإنشغال
                  </th>
                  <th scope="col" class="py-3 px-4">
                    بتاريخ
                  </th>
                  <th scope="col" class="py-3 px-4">
                    حالة الطلب
                  </th>
                  <th scope="col" class="py-3 px-4">
                  </th>
                </tr>
              </thead>
              <tbody>
                <?php
                foreach ($result as $line) {
                  ?>
                  <tr class="bg-primaryDarker hover:bg-green-800">
                    <td scope="row" class="py-4 px-2 font-bold text-center text-orange-300 ">
                      <?php echo $line[12]; ?>
                      <!-- id -->
                    </td>
                    <td scope="row" class="py-4 px-2 font-medium text-gray-900 whitespace-nowrap">
                      <?php echo $line[0]; ?>
                      <!-- daira -->
                    </td>
                    <td class="py-4 px-2">
                      <?php echo $line[1]; ?>
                      <!-- commune -->
                    </td>
                    <td class="py-4 px-2">
                      <?php
                      if ($line[2] == 00) {
                        echo $line[3];
                      } else {
                        echo $line[2];
                      }
                      ?>
                      <!-- village -->
                    </td>
                    <td class="py-4 px-2">
                      <?php echo $line[4]; ?>
                      <!-- secteur -->
                    </td>
                    <td class="py-4 px-2">
                      <?php echo $line[5]; ?>
                      <!-- typeinchighal -->
                    </td>
                    <td class="py-4 px-2">
                      <?php echo $line[6]; ?>
                      <!-- typeuser -->
                    </td>
                    <td class="py-4 px-2 whitespace-normal">
                      <?php echo $line[7]; ?>
                      <!-- detaille -->
                    </td>
                    <td class="py-4 text-xs">
                      <?php echo date("Y-m-d", strtotime($line[8])); ?>
                      <!-- date add -->
                    </td>
                    <td class="py-4 px-4 ">
                    <?php
               switch ($line[9]) {
                       case 0:
                                                echo '<div class="flex flex-row justfy-between items-center">
                                                <div class="h-3.5 w-3.5  rounded-full bg-green-500 ">
                                                    <div class="h-3.5 w-3.5 animate-ping  rounded-full bg-green-500">
                                                    </div>
                                                </div>
                                                <span class="mr-3">في انتظار المعالجة </span>
                                            </div>';
                                                break;
                                            case 1:
                                                echo '<div class="flex flex-row justfy-between items-center">
                                                <div class="h-3.5 w-3.5 rounded-full bg-yellow-500 ">
                                                    <div class="h-3.5 w-3.5 animate-ping rounded-full bg-yellow-500">
                                                    </div>
                                                </div>
                                                <span class="mr-3">قيد المعالجة</span>
                                            </div>';
                                                break;
                                            case 2:
                                                echo '<div class="flex flex-row justfy-between items-center">
                                                    <div class="h-3.5 w-3.5 rounded-full bg-blue-500 ">
                                                        <div class="h-3.5 w-3.5 rounded-full bg-blue-500">
                                                        </div>
                                                    </div>
                                                    <span class="mr-3">معالج </span>
                                                </div>';
                                                break;
                                            case 3:
                                                echo '<div class="flex flex-row justfy-between items-center">
                                                        <div class="h-3.5 w-3.5 rounded-full bg-red-500 ">
                                                            <div class="h-3.5 w-3.5 rounded-full bg-red-500">
                                                            </div>
                                                        </div>
                                                        <span class="mr-3">مرفوض </span>
                                                    </div>';
                                                break;
                                        }
                                        ?>
                    </td>
                    <td class="py-4 pl-4">
                      <a href="details_inchighal.php?id_inchighal=<?php echo $line[12]; ?>&mode=search&"
                        type="button" class="font-bold text-orange-300 hover:underline"> عرض</a>
                    </td>
                  </tr>
                  <?php
                }
                ?>
              </tbody>
              <tfoot>

            <td colspan="11" class="justify-center items-center">
                <?php echo $pagination->createLinks(); ?>
            </td>

        </tfoot>
            </table>
         
          </div>
          <!-- pagination -->

        
          <!--end paginaiton -->
          <div class="flex content-center">
            <?php
        } else
          echo "لا توجد نتيجة متطابقة مع البيانات المدخلة"; ?>

        </div>
      </div>
      <!-- fin inchighalet -->

    </div>

  </div>
</div>
<?php
include('layout.footer.php');
?>