<?php
include('../auth_session_admin.php');
include_once('../pagination.class.php');
include('../../config/db.php');

$baseURL = 'getData.php';
$offset = !empty($_POST['page']) ? $_POST['page'] : 0;
$limit = 10;

if (isset($_POST['page'])) {

    $query = "SELECT daira.libdairaar,commune.libcomar,village.libvillagear,`adresse`,`secteur`,`typeinchighal`,
    `typeuser`,`object`,`dateadd`,`alreadyopen`,inchighalat.`daira`,inchighalat.`commune` ,id 
    FROM `inchighalat`
                JOIN daira ON daira.coddaira = inchighalat.daira
                JOIN commune ON commune.codcom = inchighalat.commune
                LEFT JOIN village ON (inchighalat.village = village.idvillage AND village.codcom = inchighalat.commune)";
    // $array = array_map('intval', explode(',', $alreadyopen));
    // $array = implode("','", $array);
    // $query .= (strpos($query, 'WHERE') !== false) ? " AND " : " WHERE ";

    if (isset($_POST["inchighal"]) && $_POST["inchighal"] != "") {
        $alreadyopen = $_POST["inchighal"];
        $query .= (strpos($query, 'WHERE') !== false) ? " AND " : " WHERE ";
        $query .= " `alreadyopen` IN ( $alreadyopen) ";
    }
    if (isset($_POST['daira']) && $_POST["daira"] != "") {
        $query .= (strpos($query, 'WHERE') !== false) ? " AND " : " WHERE ";
        $query .= " inchighalat.daira =" . $_POST['daira'];
    }
    if (isset($_POST['commune']) && $_POST["commune"] != "") {
        $query .= (strpos($query, 'WHERE') !== false) ? " AND " : " WHERE ";
        $query .= " inchighalat.commune =" . $_POST['commune'];
    }
    if (isset($_POST['secteur']) && $_POST["secteur"] != "") {
        $query .= (strpos($query, 'WHERE') !== false) ? " AND " : " WHERE ";
        $query .= " inchighalat.secteur ='" . $_POST['secteur'] . "'";
    }
    if (isset($_POST['datestart']) && $_POST["datestart"] != "" && $_POST['dateend'] && $_POST["dateend"] != "") {
        $query .= (strpos($query, 'WHERE') !== false) ? " AND " : " WHERE ";
        $query .= " CAST(inchighalat.dateadd As Date)  between '" . $_POST['datestart'] . "' AND '" . $_POST['dateend'] . "'";
    }

    $query2 = $con->query($query);
    $rowCount = mysqli_num_rows($query2);

    $query .= " ORDER BY alreadyopen asc ,dateadd asc";
    $query .= " LIMIT $offset, $limit";
    $query_run = mysqli_query($con, $query);


    // Initialize pagination class 
    $pagConfig = array(
        'baseURL' => $baseURL,
        'totalRows' => $rowCount,
        'perPage' => $limit,
        'currentPage' => $offset,
        'contentDiv' => 'dataContainer',
        'link_func' => 'searchFilter'
    );
    $pagination = new Pagination($pagConfig);
    //----- end setup pagination

    $result = [];

    while ($row = $query_run->fetch_row()) {
        $result[] = $row;
    }
}
?>

<table class="overflow-scroll table-auto w-full text-sm mt-4 text-right text-gray-500 rounded-lg bg-primaryDarkest">
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
            <th scope="col" class="py-3 px-4">...
            </th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (count($result) > 0) {
            foreach ($result as $line) {
                ?>
                <tr class="open-inchighal bg-primaryDarker hover:bg-green-800 hover:scale-105 hover:cursor-pointer"
                    data-href="details_inchighal.php?id_inchighal=<?php echo $line[12]; ?>">
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
                        <a href="details_inchighal.php?id_inchighal=<?php echo $line[12] . '&page=' . $offset; ?>" type="button"
                            class="font-bold text-orange-300 hover:underline"> عرض</a>
                    </td>
                    <?php
                //target="_blank"
            }
        } else {
            ?>
                <td class="py-4 pl-4 text-xl text-orange-300">
                    لاتوجد إنشغالبات
                </td>
                <?php
        }
        ?>
        </tr>

    </tbody>
    <tfoot>
        <td colspan="11" class="justify-center items-center">
            <?php echo $pagination->createLinks(); ?>
        </td>

    </tfoot>

</table>

<div class="loading-overlay " style="display: none;">
    <div class="overlay-content">تحميل...</div>
</div>
<script>
      $(document).ready(function ($) {
                        $(".open-inchighal").click(function () {
                            window.location = $(this).data("href");
                        });
                    });
                </script>