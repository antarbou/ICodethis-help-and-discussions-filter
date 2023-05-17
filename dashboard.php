<?php
ob_start();
include('layout.main.php');
include('auth_session_admin.php');
include_once('pagination.class.php');

if ($_SESSION['isAdmin'] != 1 and $_SESSION['isAdmin'] != 2) {
    header('location: ../index.php');
}

$baseURL = 'scripts/getData.php';
$limit = 10;

$query = $con->query("SELECT COUNT(*) As rowNum FROM `inchighalat`");
$result = $query->fetch_assoc();
$rowCount = $result['rowNum'];

$pagConfig = array(
    'baseURL' => $baseURL,
    'totalRows' => $rowCount,
    'perPage' => $limit,
    'contentDiv' => 'datafilter',
    'link_func' => 'searchFilter'
);
$pagination = new Pagination($pagConfig);

// bring data daira 
//----------------------------- bring daira from table daira
$dairaAll = "SELECT `coddaira`,`libdairaar` FROM `daira` ";
$resulteDaira = mysqli_query($con, $dairaAll);

//--------end daira---------
$usersession = $_SESSION['citoyen'];
$iduser = $_SESSION['id'];
$create_datetime = date("Y-m-d H:i:s");

$query = "SELECT daira.libdairaar,commune.libcomar,village.libvillagear,`adresse`,`secteur`,`typeinchighal`,
`typeuser`,`object`,`dateadd`,`alreadyopen`,inchighalat.`daira`,inchighalat.`commune` ,id 
FROM `inchighalat`
            JOIN daira ON daira.coddaira = inchighalat.daira
            JOIN commune ON commune.codcom = inchighalat.commune
            LEFT JOIN village ON (inchighalat.village = village.idvillage AND village.codcom = inchighalat.commune)";
$query .= " ORDER BY alreadyopen asc ,dateadd asc";
$query .= " LIMIT $limit";
$query_run = mysqli_query($con, $query);
$result = [];
while ($row = $query_run->fetch_row()) {
    $result[] = $row;
}
ob_end_flush();
if (isset($_GET["update_pass_user"]) and $_GET["update_pass_user"] == "success") {
    ?>
    <script>
        swal({
            title: "تم",
            text: "تغيير كلمة السر بنجاح",
            icon: "success",
            button: "حسنا",
        });
        window.history.replaceState({}, document.title, "/" + "dashboard.php");
    </script>
    <?php
}

if (isset($_GET["update_pass_admin"]) and $_GET["update_pass_admin"] == "success") {
    ?>
    <script>
        swal({
            title: "تم",
            text: "تغيير كلمة السر بنجاح",
            icon: "success",
            button: "حسنا",
        });
    </script>
    <?php
}
if (isset($_GET["update_profile_admin"]) and $_GET["update_profile_admin"] == "success") {
    ?>
    <script>
        swal({
            title: "تم",
            text: "تغيير بياناتك بنجاح",
            icon: "success",
            button: "حسنا",
        });
    </script>
    <?php
}
?>
<!-- this code to fill communes -->
<script>
    $(document).ready(function () {
        $("#daira").change(function () {
            var dairaid = $(this).val();
            $.ajax({
                url: '../scripts/listcommunes.php',
                type: 'post',
                data: {
                    daira: dairaid
                },
                dataType: 'json',
                success: function (response) {
                    var len = response.length;
                    $("#commune").empty();
                    $("#village").empty();
                    $("#village").append("<option selected value=\"\">الكل</option>");
                    $("#village").attr("disabled", true);
                    $("#commune").removeAttr("disabled"); // to enable select communes
                    $("#adresse").addClass("hidden");
                    $("#commune").append("<option selected value=\"\">الكل </option>");
                    for (var i = 0; i < len; i++) {
                        var codecom = response[i]['codecom'];
                        var libcom = response[i]['libcom'];
                        $("#commune").append("<option value='" + codecom + "'>" + libcom + "</option>");
                    }
                }
            });
        });
        // communes
        $("#commune").change(function () {
            var idcommune = $(this).val();
            $.ajax({
                url: '../scripts/listvillage.php',
                type: 'post',
                data: {
                    commune: idcommune
                },
                dataType: 'json',
                success: function (response) {
                    var len = response.length;
                    $("#village").empty();
                    $("#village").removeAttr("disabled"); // to enable select village
                    $("#adresse").addClass("hidden");
                    $("#village").append("<option selected value=\"\">الكل</option>");
                    for (var i = 0; i < len; i++) {
                        var idvillage = response[i]['idvillage'];
                        var libvillage = response[i]['libvillagear'];
                        $("#village").append("<option value='" + idvillage + "'>" + libvillage + "</option>");
                    }
                }
            });
        });
    });
   //-------------
</script>
<!---->

<div class="flex-col relative flex md:flex-row min-h-screen
            justify-center bg-primaryAltDarkest shadow-lg ">
    <!--Menu side Bare-->
    <?php
    include('Menu.php');
    ?>
    <!-- Contenent -->
    <div id="contents"
        class="flex-col overflow-x-auto text-grayLighter w-4/5 flex flex-grow items-right px-4 md:p-4 md:mx-0">
        <!---->
        <div id="inchighalat" class="overflow-hidden">
            <!-- search by fields -->
            <label
                class="relative w-28 flex flex-row gap-2 justify-between items-center 
            px-4 mx-8 font-semibold mb-2 bg-primaryDarker border-solid border-2 border-orange-300  p-2 rounded-lg shadow-md">
                تصفية
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="show-filter w-5 h-5 cursor-pointer">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </label>
            <div class="show-filter-btn hidden flex-col pb-2 px-6 mt-5 mx-5 border-solid border-2 border-orange-300 
                                                    rounded-lg pt-8 transition duration-150 ease-in-out mb-6">
                <script>
                    const filter = document.querySelector('.show-filter');
                    const btnfilter = document.querySelector('.show-filter-btn');
                    filter.addEventListener('click', () => {
                        btnfilter.classList.toggle('hidden');
                        filter.classList.toggle('rotate-180');
                    })
                </script>
                <div class="flex flex-col gap-5 sm:flex-row justify-between">
                    <!-- Daira-->
                    <div class="mb-2 flex-grow">
                        <label for="daira" class="block mb-2 text-sm font-semibold text-gray-900 ">الدائرة</label>
                        <select onchange="searchFilter();" required id="daira" name="daira"
                            class="w-full bg-primary border border-gray-300 text-gray-900 text-sm rounded-lg 
                                                                                                                 focus:ring-blue-500 focus:border-blue-500  p-1">
                            <option selected value="">إختر الدائرة</option>
                            <?php
                            while ($libdaira = mysqli_fetch_array($resulteDaira)):
                                ;
                                ?>
                                <option value="<?php echo $libdaira["coddaira"]; ?>">
                                    <?php echo $libdaira["libdairaar"]; ?>
                                </option>
                            <?php endwhile; ?>

                        </select>
                    </div>
                    <!-- Communes-->
                    <div class="mb-2 flex-grow">
                        <label for="commune" class=" block mb-2 text-sm font-semibold text-gray-900 ">البلدية</label>
                        <select onchange="searchFilter();" disabled required name="commune" id="commune"
                            class=" w-full bg-primary border border-gray-300 text-gray-900 text-sm rounded-lg 
                                                                                                                 focus:ring-blue-500 focus:border-blue-500  p-1">
                            <option selected value="">الكل </option>
                        </select>
                    </div>
                    <!-- village-->
                    <div class="mb-2 flex-grow">
                        <label for="village" class="w-full block mb-2 text-sm font-semibold text-gray-900 ">تحديد
                            المكان</label>
                        <select onchange="searchFilter();" disabled required id="village" name="village"
                            class="w-full bg-primary border border-gray-300 text-gray-900 text-sm rounded-lg 
                                                                                                                 focus:ring-blue-500 focus:border-blue-500  p-1 ">
                            <option selected value="">الكل</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-row gap-4 items-center">
                    <div class="flex flex-col w-2/4">
                        <div class="flex flex-col mb-2 ">
                            <label for="secteur" class="block mb-2 text-sm font-semibold text-gray-900 ">القطاع</label>
                            <select onchange="searchFilter();" required id="secteur" name="secteur"
                                class="bg-primary border border-gray-300 text-gray-900 text-sm rounded-lg 
                                                                                focus:ring-blue-500 focus:border-blue-500 block p-1 ">
                                <option selected value="">إختر القطاع المناسب</option>
                                <option value="الداخلية والجماعات المحلية والتهيئة العمرانية">الداخلية والجماعات المحلية
                                    والتهيئة العمرانية</option>
                                <option value="العدل">العدل</option>
                                <option value="الصحة">الصحة</option>
                                <option value="الشؤون الخارجية">الشؤون الخارجية</option>
                                <option value="الشؤون الدينية والأوقاف">الشؤون الدينية والأوقاف</option>
                                <option value="التضامن الوطني والعائلة">التضامن الوطني والعائلة</option>
                                <option value="الفلاحة والتنمية الريفية">الفلاحة والتنمية الريفية</option>
                                <option value="التجارة">التجارة</option>
                                <option value="الثقافة">الثقافة</option>
                                <option value="الوظيف العمومية">الوظيف العمومية</option>
                                <option value="المالية">المالية</option>
                                <option value="التربية الوطنية">التربية الوطنية</option>
                                <option value="التعليم العالي والبحث العلمي">التعليم العالي والبحث العلمي</option>
                                <option value="السكن والعمران">السكن والعمران</option>
                                <option value="التعمير">التعمير</option>
                                <option value="الموارد المائية">الموارد المائية</option>
                                <option value="الأشغال العمومية">الأشغال العمومية </option>
                                <option value="التكوين والتعليم المهنيين">التكوين والتعليم المهنيين</option>
                                <option value="العمل والضمان الإجتماعي">العمل والضمان الإجتماعي</option>
                                <option value="الطاقة والمناجم">الطاقة والمناجم</option>
                                <option value="الشبيبة والرياضة">الشبيبة والرياضة</option>
                                <option value="الصيد والموارد الصيدية">الصيد والموارد الصيدية</option>
                                <option value="البريد وتكنولوجيات الإعلام والإتصال">البريد وتكنولوجيات الإعلام والإتصال
                                </option>
                                <option value="النقل">النقل</option>
                                <option value="السياحة والصناعة التقليدية">السياحة والصناعة التقليدية</option>
                                <option value="الصناعة و ترقية الإستثمارات">الصناعة و ترقية الإستثمارات</option>
                                <option value="التهيئة العمرانية والبيئة">التهيئة العمرانية والبيئة</option>
                            </select>
                        </div>
                        <div class="flex flex-col item-centers mb-2  ">
                            <div class="flex flex-row ">
                                <label for="secteur" class="flex-grow mb-2 text-sm font-semibold text-gray-900 ">الفترة
                                    الزمنية من </label>

                                <label for="secteur" class="flex-grow mb-2 text-sm font-semibold text-gray-900 ">إلى
                                </label>
                            </div>
                            <div class="flex flex-row gap-5 justify-center">
                                <input type="date" id="datestart" name="datestart" onchange="searchFilter();"
                                    class=" w-full p-2 text-sm text-gray border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"
                                    required value="" />
                                <input type="date" id="dateend" name="dateend" onchange="searchFilter();"
                                    class=" w-full p-2 text-sm text-gray border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"
                                    required value="" />
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col  w-2/4  gap-4 items-top p-6 border rounded-lg border-orange-300">
                        <div class="flex flex-row  items-right">
                            <label for="" class="font-bold text-white text-xl">حالة الملف</label>
                        </div>
                        <div class="flex flex-col md:flex-row flex-wrap gap-4 justify-center">
                            <div class="flex flex-row">
                                <input onchange="searchFilter();" class="h-4 w-4 border border-orange-300 rounded-sm bg-white checked:bg-orange-600
                                                     checked:border-orange-600 focus:outline-none transition duration-200 mt-1 align-top 
                                                     bg-no-repeat bg-center bg-contain float-right ml-2 cursor-pointer"
                                    type="checkbox" value="0" id="inchighal0" name="inchighal"></input>
                                <label class=" inline-block text-gray-800 cursor-pointer" for="inchighal0">
                                    في انتظار المعالجة
                                </label>
                            </div>
                            <div class="flex flex-row">
                                <input onchange="searchFilter();" class="h-4 w-4 border border-gray-300 rounded-sm bg-white checked:bg-orange-600
                                                     checked:border-orange-600 focus:outline-none transition duration-200 mt-1 align-top 
                                                     bg-no-repeat bg-center bg-contain float-right ml-2 cursor-pointer"
                                    type="checkbox" value="1" id="inchighal1" name="inchighal"></input>
                                <label class="inline-block text-gray-800 cursor-pointer" for="inchighal1">
                                    قيد المعالجة
                                </label>
                            </div>
                            <div class="flex flex-row">
                                <input onchange="searchFilter();" class="h-4 w-4 border border-gray-300 rounded-sm bg-white checked:bg-orange-600
                                                     checked:border-orange-600 focus:outline-none transition duration-200 mt-1 align-top 
                                                     bg-no-repeat bg-center bg-contain float-right ml-2 cursor-pointer"
                                    type="checkbox" value="2" id="inchighal2" name="inchighal"></input>
                                <label class=" inline-block text-gray-800 cursor-pointer" for="inchighal2">
                                    معالج
                                </label>
                            </div>
                            <div class="flex flex-row">
                                <input onchange="searchFilter();" class="h-4 w-4 border border-gray-300 rounded-sm bg-white checked:bg-orange-600
                                                     checked:border-orange-600 focus:outline-none transition duration-200 mt-1 align-top 
                                                     bg-no-repeat bg-center bg-contain float-right ml-2 cursor-pointer"
                                    type="checkbox" value="3" id="inchighal3" name="inchighal"></input>
                                <label class=" inline-block text-gray-800 cursor-pointer" for="inchighal3">
                                    مرفوض
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!---->
            <!--end panel search -->
            <script>
                function searchFilter(page_num) {
                    var test = new Array();
                    $("input[name='inchighal']:checked").each(function () {
                        test.push($(this).val());
                    });
                    page_num = page_num ? page_num : 0;
                    var daira = $('#daira').val();
                    var commune = $('#commune').val();
                    var secteur = $('#secteur').val();
                    var datestart = $('#datestart').val();
                    var dateend = $('#dateend').val();
                    var inchighal = test;
                    $.ajax({
                        type: 'POST',
                        url: 'scripts/getData.php',
                        data: 'page=' + page_num + '&daira=' + daira + '&commune=' + commune + '&inchighal=' + inchighal +
                            '&secteur=' + secteur + "&datestart=" + datestart + "&dateend=" + dateend,
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
                <div id="dataContainer" class="overflow-auto flex flex-col rounded-lg">
                    <!-- <div class="flex item-centers justify-center font-semibold text-xl">
                                                            <?php
                                                            echo "عدد الإنشغالات :  " . $rowCount;
                                                            ?>
                                                        </div> -->
                    <table
                        class="overflow-scroll table-auto w-full text-xs xl:text-sm mt-4 text-right text-gray-500 rounded-lg bg-primaryDarkest">
                        <thead class="text-xs xl:text-sm text-center text-gray-700 m-2">
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
                            foreach ($result as $line) {
                                ?>
                                <tr class="open-inchighal bg-primaryDarker hover:bg-green-800 hover:scale-105 hover:cursor-pointer"
                                    data-href="details_inchighal.php?id_inchighal=<?php echo $line[12]; ?>">
                                    <td scope="row" class="py-4 px-2 font-bold text-center text-orange-300">
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
                                        <a href="details_inchighal.php?id_inchighal=<?php echo $line[12]; ?>" type="button"
                                            class="font-bold text-orange-300 hover:underline"> عرض</a>
                                    </td>
                                </tr>

                                <?php
                            }
                            ?>
                        </tbody>
                        <tfoot class="w-full p-2">
                            <td colspan="9" class="justify-center items-center">
                                <?php echo $pagination->createLinks(); ?>
                            </td>
                        </tfoot>
                    </table>

                    <div class="loading-overlay " style="display: none;">
                        <div class="overlay-content">تحميل...</div>
                    </div>
                </div>
                <script>
                    $(document).ready(function ($) {
                        $(".open-inchighal").click(function () {
                            window.location = $(this).data("href");
                        });
                    });
                </script>
                <?php
            } else {
                echo " لاتوجد طلبات إنشغالات لحد الآن.";
            }
            ?>
        </div>
        <!-- fin inchighalet -->
    </div>


</div>
<?php
include('layout.footer.php');
?>