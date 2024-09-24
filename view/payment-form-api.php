<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<link rel="stylesheet" href="<?php echo plugin_url ?>/assets/css/style.css">
<link rel="stylesheet" href="<?php echo plugin_url ?>/assets/css/bootstrap-icons.css">
<main class="container form-signin mt-0 pt-0 shadow p-4 p-md-3">
    <style>
        .input {
            width: auto !important;
        }
    </style>
    <div id="index">
        <form action="<?php echo get_site_url() ?>/wc-api/parolapara_start_payment" id="perForm" method="post">
            <input type="hidden" name="d3Yonlendir" value="<?php echo $order_id ?>">
            <input type="hidden" name="secilen_taksit" value="1">
            <input type="hidden" name="odenecek_tutar" value="<?php echo $order->get_total() ?>">
            <div class="input-group mb-3">

            <span class="input-group-text text-primary" style="min-height: 50px !important;" id="basic-addon12">
                <i class="bi bi-person fs-4"></i>
            </span>

                <input type="text" class="form-control input" placeholder="Kart Üzerindeki Ad Soyad" id="name"
                       name="cardHolderName"
                       autocomplete="cc-name" aria-describedby="basic-addon12">

            </div>
            <span id="validationwarningname" class="text-danger input-group"></span>
            <div class="input-group mb-3">
            <span class="input-group-text text-primary" style="min-height: 50px !important;" id="basic-addon1">
                <i class="bi bi-credit-card-2-front fs-4"></i>
            </span>
                <input type="text" inputmode="numeric" class="form-control input" keyup="formatCard(this)"
                       placeholder="Kart Numarası" aria-label="number" id="number" name="cardNumber"
                       autocomplete="cc-number" pattern="[0-9\s]{4} [0-9\s]{4} [0-9\s]{4} [0-9\s]{4}" maxlength="19"
                       aria-describedby="basic-addon1">
            </div>
            <span id="validationwarningcard" class="text-danger input-group"></span>
            <div class="input-group g-1">
                <div class="col-8">
                    <div class="input-group mb-3">
                    <span class="input-group-text text-primary" style="min-height: 50px !important;" id="basic-addon13">
                        <i class="bi bi-calendar3 fs-4"></i>
                    </span>
                        <input type="text" inputmode="numeric" class="form-control input"
                               style="max-width: 60px !important;"
                               maxlength="2" autocomplete="cc-exp-month" placeholder="Ay" name="ay" id="ay"
                               aria-label="Ay">
                        <span class="input-group-text">/</span>
                        <input type="text" inputmode="numeric" class="form-control input" placeholder="Yıl"
                               maxlength="4"
                               aria-label="Yıl" autocomplete="cc-exp-year" name="yil" id="yil" aria-invalid="false"
                               style="max-width: 80px !important;">
                    </div>
                </div>
                <div class="col-4">
                    <div class="input-group mb-3" style="flex-wrap: nowrap">
                        <span class="input-group-text text-primary" style="min-height: 50px !important;" id="basic-addon14">
                            <i class="bi bi-credit-card-2-back fs-4"></i>
                        </span>
                        <input
                                type="text"
                                inputmode="numeric" class="form-control" placeholder="CVV" id="cvv"
                                name="cvv" autocomplete="cc-csc" aria-label="cvv" maxlength="4"
                                aria-describedby="basic-addon14" spellcheck="false" mask="000">
                    </div>
                </div>
                <span id="validationwarningcvv" class="text-danger input-group"></span>
                <div class="row g-2 mb-2 text-center" id="cardProgram">
                    <div class="col-4">
                        <img src="<?php echo plugin_url ?>/images/mastercard.png" class="img-fluid"
                             style="max-height:40px;">
                    </div>
                    <div class="col-4">
                        <img src="<?php echo plugin_url ?>/images/visa-yeni2.png" class="img-fluid"
                             style="max-height:40px;">
                    </div>
                    <div class="col-4">
                        <img src="<?php echo plugin_url ?>/images/troy.png" class="img-fluid" style="max-height:40px;">
                    </div>
                </div>
            </div>
            <p id="taksitp">Taksit seçenekleri geçerli kart bilgilerini girdikten sonra görüntülenecektir.</p>
            <div class="row" id="taksit">
            </div>
            <div id="indexbutton">
                <button class="w-100 btn btn-lg btn-primary" type="button" onclick="validateForm();"
                        name="paybuttontext" id="paybuttontext"
                        title="Taksit Seçenekleri"> <?php echo $order->get_total() ?> ₺ Öde
                </button>
            </div>
        </form>
    </div>
</main>
<script src="https://code.jquery.com/jquery-3.6.1.js"
        integrity="sha256-3zlB5s2uwoUzrXK3BT7AX3FyvojsraNFxCc2vC/7pNI=" crossorigin="anonymous"></script>
<script>
    function setcol(sel, text) {
        if (text != '') {
            const t = document.createElement("td");
            t.innerText = text;
            sel.appendChild(t);
        }
    }

    var rows = "";
    const col1s = document.createElement("tr");
    const col2s = document.createElement("tr");
    const col3s = document.createElement("tr");
    const col4s = document.createElement("tr");
    const col5s = document.createElement("tr");
    const col6s = document.createElement("tr");
    const col7s = document.createElement("tr");
    const col8s = document.createElement("tr");
    const col9s = document.createElement("tr");
    const col10s = document.createElement("tr");
    const col11s = document.createElement("tr");
    const col12s = document.createElement("tr");
    var acces;
    var bonus;
    var combo;
    var cardfinans;
    var maximum;
    var paraf;
    var saglam;
    var world;

    function resetList() {
        col1s.innerHTML = "";
        col2s.innerHTML = "";
        col3s.innerHTML = "";
        col4s.innerHTML = "";
        col5s.innerHTML = "";
        col6s.innerHTML = "";
        col7s.innerHTML = "";
        col8s.innerHTML = "";
        col9s.innerHTML = "";
        col10s.innerHTML = "";
        col11s.innerHTML = "";
        col12s.innerHTML = "";
        setcol(col1s, 'Peşin');
        setcol(col2s, '2 Taksit ');
        setcol(col3s, '3 Taksit ');
        setcol(col4s, '4 Taksit ');
        setcol(col5s, '5 Taksit ');
        setcol(col6s, '6 Taksit ');
        setcol(col7s, '7 Taksit ');
        setcol(col8s, '8 Taksit ');
        setcol(col9s, '9 Taksit ');
        setcol(col10s, '10 Taksit ');
        setcol(col11s, '11 Taksit ');
        setcol(col12s, '12 Taksit ');
    }

    function setBankList(res) {
        if (res != undefined) {
            console.log(res);
            for (i = 0; i < res.DATA.length; i++) {
                var item = res.DATA[i];
                console.log("item" + item);
                var amountt = "1.00";
                if ('NONE' == PAYMENT_ROUTER_PAY_BY_LINK && 'false' == 'true') {
                    amountt = "" + parseFloat($("#inputAmount").val()).toFixed(2);
                }
                var tutar = orand(item.COMMISION, amountt.replace(",", "."));
                var textvalue = "" + (tutar / item.INSTALLMENT).toFixed(2) + " " + item.currency + " x " + item.INSTALLMENT + "";
                if ('NONE' == PAYMENT_ROUTER_PAY_BY_LINK) {
                    if (i > 0) textvalue = "";
                }
                switch (item.INSTALLMENT) {
                    case "1":
                        setcol(col1s, textvalue)
                        break;
                    case "2":
                        setcol(col2s, textvalue)
                        break;
                    case "3":
                        setcol(col3s, textvalue)
                        break;
                    case "4":
                        setcol(col4s, textvalue)
                        break;
                    case "5":
                        setcol(col5s, textvalue)
                        break;
                    case "6":
                        setcol(col6s, textvalue)
                        break;
                    case "7":
                        setcol(col7s, textvalue)
                        break;
                    case "8":
                        setcol(col8s, textvalue)
                        break;
                    case "9":
                        setcol(col9s, textvalue)
                        break;
                    case "10":
                        setcol(col10s, textvalue)
                        break;
                    case "11":
                        setcol(col11s, textvalue)
                        break;
                    case "12":
                        setcol(col12s, textvalue)
                        break;
                }
            }
        }
    }


    function orand(val, amount) {
        console.log("authAmount val : " + val + " amount:" + amount + " " + amount.length);
        var carpan = 10;
        if (amount.length == 4)
            carpan = 100;
        console.log(carpan);
        amount = amount * carpan;
        console.log(amount);
        var authAmount = parseFloat(amount / (1 - (parseFloat(val.replace(",", ".")) / amount))).toFixed(2);
        console.log("authAmount amount : " + authAmount);
        authAmount = authAmount / carpan;
        console.log("authAmount amount : " + authAmount);
        return authAmount;
    };
</script>
<script>
    var PAYMENT_ROUTER_PAY_BY_LINK = "PAY_BY_LINK";

    function _isValidCharacter(o) {
        o.value = o.value.replace(/[^\d,]/g, '');
    }

    function IsNullOrEmty($str) {
        return true;
    }

    function validateForm(s) {
        $("#validationwarningcard").html("");
        $("#validationwarningname").html("");
        $("#validationwarningcvv").html("");
        var errorExist = false;
        var forkey = true;
        if (forkey) {
            if ($("#mysubmitbutton")) {
                var mysubmitbuttonText = $("#mysubmitbutton").text();
                if (mysubmitbuttonText != null) {
                    mysubmitbuttonText = mysubmitbuttonText.trim();
                }
                if (mysubmitbuttonText != null && (mysubmitbuttonText.startsWith("0,00") || mysubmitbuttonText.startsWith("0.00"))) {
                    errorExist = true;
                    $("#validationwarninginputAmount").html("<h5>Lütfen ödenecek tutar giriniz.</h5>");
                }
            }
            var numberWithoutSpace = $("#number").val().replace(/\s/g, '');
            if (numberWithoutSpace.length < 13 || numberWithoutSpace.length > 19) {
                errorExist = true;
                $("#validationwarningcard").html("<h5> Lütfen geçerli bir kart giriniz.</h5>");
            }
            if ("nop" == "4" && (numberWithoutSpace.charAt(0) != "4")) {//visa
                errorExist = true;
                $("#validationwarningcard").html("<h5>Kartınız bir VISA kart olmalıdır.</h5>");
            }
            if ("nop" == "5" && (numberWithoutSpace.charAt(0) != "5")) {//mastercard
                errorExist = true;
                $("#validationwarningcard").html("<h5>Kartınız bir MASTERCARD olmalıdır.</h5>");
            }
            if (!("nop" == "nop")) {
                var bins = "nop".split("|");
                var binMatched = false;
                for (var i = 0; i < bins.length; i++) {
                    if (numberWithoutSpace.substring(0, 6) == bins[i].trim()) {
                        binMatched = true;
                    }
                }
                if (!binMatched) {
                    errorExist = true;
                    $("#validationwarningcard").html("<h5>Kampanya kapsamındaki kredi – banka kartınız ile ödeme yapmanız gerekmektedir.</h5>");
                }
            }
            if ($("#name").val() == "") {
                errorExist = true;
                $("#validationwarningname").html("<h5>Lütfen ad soyad giriniz</h5>");
            }
            if ($("#ay").val() == "") {
                errorExist = true;
                $("#validationwarningcvv").html("<h5>Lütfen son kullanma tarihini giriniz</h5>");
            } else {
                if ($("#ay").val() < 1 || $("#ay").val() > 12) {
                    errorExist = true;
                    $("#validationwarningcvv").html("<h5>Lütfen son kullanma tarihini giriniz</h5>");
                }
            }
            if ($("#yil").val() == "") {
                errorExist = true;
                $("#validationwarningcvv").html("<h5>Lütfen son kullanma tarihini giriniz</h5>");
            }
            if ($("#ay").val() != "" && $("#yil").val() != "") {
                var today = new Date();
                var expday = new Date();
                expday.setFullYear($("#yil").val(), $("#ay").val(), 1);
                if (expday < today) {
                    errorExist = true;
                    $("#validationwarningcvv").html("<h5>Son kullanma tarihi geçmiş bir tarih olamaz</h5>");
                }
            }
            if ("False" == "True")
                if ($("#cvv").val().length < 3 || $("#cvv").val().length > 4) {
                    errorExist = true;
                    $("#validationwarningcvv").html("<h5>Lütfen geçerli bir CVV giriniz</h5>");
                }
        } else {
            if (IsNullOrEmty($("#paraBirimi").val())) {
                errorExist = true;
                $("#validationwarningcvv").html("<h5>Your choice of currency to trade.</h5>");
            }
            var numberWithoutSpace = $("#number").val().replace(/\s/g, '');
            if (numberWithoutSpace.length < 13 || numberWithoutSpace.length > 19) {
                errorExist = true;
                $("#validationwarningcard").html("<h5> Lütfen geçerli bir kart giriniz.</h5>");
            }
            if ("nop" == "4" && (numberWithoutSpace.charAt(0) != "4")) {//visa
                errorExist = true;
                $("#validationwarningcard").html("<h5>Kartınız bir VISA kart olmalıdır.</h5>");
            }
            if ("nop" == "5" && (numberWithoutSpace.charAt(0) != "5")) {//mastercard
                errorExist = true;
                $("#validationwarningcard").html("<h5>Kartınız bir MASTERCARD olmalıdır.</h5>");
            }
            if ($("#name").val() == "") {
                errorExist = true;
                $("#validationwarningname").html("<h5>Please enter your name and surname</h5>");
            }
            if ($("#cvv").val() == "") {
                errorExist = true;
                $("#validationwarningname").html("<h5>Lütfen kartınızın arkasındaki 3 haneli numarayı giriniz</h5>");
            }
            if ($("#ay").val() == "") {
                errorExist = true;
                $("#validationwarningcvv").html("<h5>Please enter expiry date</h5>");
            }
            if ($("#yil").val() == "") {
                errorExist = true;
                $("#validationwarningcvv").html("<h5>Please enter expiry date</h5>");
            }
            if ($("#ay").val() != "" && $("#yil").val() != "") {
                var today = new Date();
                var expday = new Date();
                expday.setFullYear($("#yil").val(), $("#ay").val(), 1);
                if (expday < today) {
                    errorExist = true;
                    $("#validationwarningcvv").html("<h5>Expiration date can not be a past date</h5>");
                }
            }
            if ("False" == "True")
                if ($("#cvv").val().length < 3 || $("#cvv").val().length > 4) {
                    errorExist = true;
                    $("#validationwarningcvv").html("<h5>Please enter a valid CVV</h5>");
                }
        }
        if (!errorExist) {
            $('#paybuttontext').attr("disabled", true);
            document.getElementById("perForm").submit();
        }
        return !errorExist;
    }

    function validateFormCardScope() {
        var errorExist = false;
        var forkey = false;
        if ($("#paraBirimi").html() == undefined) {
            forkey = true;
        }
        if ($("#CARD_SCOPE").val() == "I") {
            if (IsNullOrEmty($("#TERMINAL_OID_FOREIGN").val()))
                forkey = true;
            else
                forkey = false;
        }
        if (forkey) {
            var creditCards = "";
            var label = "";
            var cardName = $('input[name="cardName"]');
            for (var i = 0; i < cardName.length; i++) {
                if (cardName[i].checked) {
                    creditCards = cardName[i].value;
                    label = "#k_" + cardName[i].id;
                    break;
                }
            }
            var creditCard = $(label).html();
            var numberWithoutSpace = creditCard.replace(/\s/g, '');
            if (numberWithoutSpace == "" || numberWithoutSpace == undefined) {
                errorExist = true;
                $("#validationwarningcards").html("<h5> Lütfen geçerli bir kart giriniz.</h5>");
            }
            if ("nop" == "4" && (numberWithoutSpace.charAt(0) != "4")) {//visa
                errorExist = true;
                $("#validationwarningcards").html("<h5>Kartınız bir VISA kart olmalıdır.</h5>");
            }
            if ("nop" == "5" && (numberWithoutSpace.charAt(0) != "5")) {//mastercard
                errorExist = true;
                $("#validationwarningcards").html("<h5>Kartınız bir MASTERCARD olmalıdır.</h5>");
            }
            if (!("nop" == "nop")) {
                var bins = "nop".split("|");
                var binMatched = false;
                for (var i = 0; i < bins.length; i++) {
                    if (numberWithoutSpace.substring(0, 6) == bins[i].trim()) {
                        binMatched = true;
                    }
                }
                if (!binMatched) {
                    errorExist = true;
                    $("#validationwarningcards").html("<h5>Kampanya kapsamındaki kredi – banka kartınız ile ödeme yapmanız gerekmektedir.</h5>");
                }
            }
            if (IsNullOrEmty($('input[name="tno"]:checked').val())) {
                errorExist = true;
                $("#validationwarningcvv").html("<h5>Kart / Taksit Seciniz.!</h5>");
            }
        } else {
            if (IsNullOrEmty($("#paraBirimi").val())) {
                errorExist = true;
                $("#validationwarningcvv").html("<h5>Your choice of currency to trade.</h5>");
            }
            var numberWithoutSpace = $("#number").val().replace(/\s/g, '');
            if (numberWithoutSpace.length < 13 || numberWithoutSpace.length > 19) {
                errorExist = true;
                $("#validationwarningcards").html("<h5> Lütfen geçerli bir kart giriniz.</h5>");
            }
            if ("nop" == "4" && (numberWithoutSpace.charAt(0) != "4")) {//visa
                errorExist = true;
                $("#validationwarningcards").html("<h5>Kartınız bir VISA kart olmalıdır.</h5>");
            }
            if ("nop" == "5" && (numberWithoutSpace.charAt(0) != "5")) {//mastercard
                errorExist = true;
                $("#validationwarningcards").html("<h5>Kartınız bir MASTERCARD olmalıdır.</h5>");
            }
            if (IsNullOrEmty($('input[name="tno"]:checked').val())) {
                errorExist = true;
                $("#validationwarningcvv").html("<h5>Kart / Taksit Seciniz.!</h5>");
            }
        }
        if (!errorExist) {
            $('#mysubmitbutton').attr("disabled", true);
            $("#myModal").modal({
                keyboard: false,
                backdrop: false
            });
            document.getElementById('coverScreen').hidden = false;
            $('#cpaybuttontext').attr("disabled", true);
            document.getElementById("perForms").submit();
        }
        return !errorExist;
    }

    function hasNumbers(t) {
        var regex = /\d/g;
        return regex.test(t);
    }

    function hasJustNumbers(val) {
        var isnum = /^\d+$/.test(val);
        return isnum;
    }

    function keyEventIsNumber(event) {
        var keyCode = (event.keyCode ? event.keyCode : event.which);
        if (keyCode > 47 && keyCode < 58 || keyCode > 95 && keyCode < 112) {
            return true;
        } else {
            return false;
        }
    }

    function preventKeysForCVC(event) {
        var keyCode = (event.keyCode ? event.keyCode : event.which);
        if (keyCode > 64 && keyCode < 91 || keyCode > 105 && keyCode < 112 || keyCode > 185) {
            event.preventDefault();
        }
    }

    function updateSubmitButtonAmount(myAmount) {
        if (myAmount != undefined) {
            $("#paybuttontext").prop("disabled", false);
            $("#registerbtn").prop("disabled", false);
            $("#paybuttontext").text(myAmount + " ₺ Öde");
            $('[name="odenecek_tutar"]').val(myAmount);
        }
        updateTaksit();
    }

    function updateTaksit() {
        var sectigimTaksit = $('input[name="tno"]:checked').attr("data-sayi");
        $('[name="secilen_taksit"]').val(sectigimTaksit);
    }
</script>
<script>
    $(document).ready(function () {
        if (document.getElementById('coverScreen') != undefined)
            document.getElementById('coverScreen').hidden = true;
        $('#cvv').keydown(function (event) {
            preventKeysForCVC(event);
        });
        $('#cvv').on('paste', function (event) {
            if (!hasJustNumbers(event.originalEvent.clipboardData.getData('Text'))) {
                event.preventDefault();
            }
        });
        $('#number').keydown(function (event) {
            if (keyEventIsNumber(event)) {
                $("#validationwarningcard").html("");
            }
        });
        $('#number').keyup(function () {
            var foo = $(this).val().split(" ").join("");
            if (foo.length > 0) {
                foo = foo.match(new RegExp('.{1,4}', 'g')).join(" ");
            }
            $(this).val(foo);
        });
        $("#yil").blur(function () {
            var year = $("#yil").val();
            var d = new Date();
            var y = "" + d.getFullYear();
            if (year.length == 1) {
                $("#yil").val("" + y.substring(0, 3) + year);
            } else if (year.length == 2) {
                $("#yil").val("" + y.substring(0, 2) + year);
            } else if (year.length == 3) {
                $("#yil").val("" + y.substring(0, 1) + year);
            }
        });
        $("#number").change(function () {
            $("#taksit").html("<table class='table'><tr class='table-primary'><th class='table-primary'>Taksit seçenekleri, geçerli kart bilgileri girildikten sonra görüntülenecektir</th></tr></table>");
        });

        function _onblur_number(amorjValue) {
            $("#taksit").html("<table class='table'><tr class='table-primary'><th class='table-primary'>Taksit seçenekleri, geçerli kart bilgileri girildikten sonra görüntülenecektir</th></tr></table>");
            if ("NONE" == "PAY_BY_LINK" && false == true) {
                $("#validationwarninginputAmount").html("");
                if (IsNullOrEmty($("#inputAmount").val())) {
                    console.log("Tutar Giriniz.");
                    $("#validationwarninginputAmount").html("<h5>Tutar boş geçilemez</h5>");
                    return;
                }
                if ($("#inputAmount").val() == "0") {
                    console.log("Tutar 0 olamaz");
                    $("#validationwarninginputAmount").html("<h5>Tutar sıfırdan büyük olmalıdır</h5>");
                    return;
                }
            }
            var numberWithoutSpace = $("#number").val().replace(/\s/g, '');
            if (numberWithoutSpace.length > 12 && numberWithoutSpace.length < 20) {
                $("#validationwarningcard").html("");
                var errorExist = false;
                if ("nop" == "4" && (numberWithoutSpace.charAt(0) != "4")) {//visa
                    errorExist = true;
                    $("#validationwarningcard").html("<h5>Kartınız bir VISA kart olmalıdır.</h5>");
                }
                if ("nop" == "5" && (numberWithoutSpace.charAt(0) != "5")) {//mastercard
                    errorExist = true;
                    $("#validationwarningcard").html("<h5>Kartınız bir MASTERCARD olmalıdır.</h5>");
                }
                if (!("nop" == "nop")) {
                    var bins = "nop".split("|");
                    var binMatched = false;
                    for (var i = 0; i < bins.length; i++) {
                        if (numberWithoutSpace.substring(0, 6) == bins[i].trim()) {
                            binMatched = true;
                        }
                    }
                    if (!binMatched) {
                        errorExist = true;
                        $("#validationwarningcard").html("<h5>Kampanya kapsamındaki kredi – banka kartınız ile ödeme yapmanız gerekmektedir.</h5>");
                    }
                }
                if (!errorExist) {
                    $("#taksit").load("<?php echo get_site_url() ?>/wc-api/parolapara_installment", {
                        cardnumber: $("#number").val(),
                        order_id: '<?php echo $order_id ?>',
                    }, function () {
                        if ($("#instalmentTd1").html() != undefined) {
                            $("#instextid").show();
                        }
                        if ('false' == 'true') {
                            $("#cardStorage").show();
                        }
                        $("#taksitp").html("Taksit Seçenekleri");
                        if ($("#instalmentTd0").html() != undefined) {
                            $("#paybuttontext").text($("#instalmentTd0")[0].children[0].innerHTML + " Öde");
                            if ($("#registerbtn") != undefined) {
                                $("#registerbtn").prop("disabled", false);
                            }
                        } else {
                            if ($("#foreignPaymenAllowShow").html() == undefined) {
                                if ('NONE' == PAYMENT_ROUTER_PAY_BY_LINK) {
                                    //$("#paybuttontext").text($("#inputAmount").val());
                                    $("#paybuttontext").text($("#inputAmount").val() + "₺ Öde");
                                } else
                                    $("#paybuttontext").text("1.00" + "₺ Öde");
                            }
                        }
                    });
                }
            } else {
                $("#validationwarningcard").html("<h5>Lütfen geçerli bir kart giriniz.</h5>");
            }
        }

        $("#number").blur(function () {
            var amorjValue = $("#amorj").val();
            _onblur_number(amorjValue);
        });
        if ($("#inputAmount")) {
            $("#inputAmount").change(function () {
                this.value = this.value.trim();
                var countOfComma = [...this.value].filter(x => x === ',').length;
                if (countOfComma >= 2) {
                    this.value = "0,00";
                }
                var keyValueModel = new Object();
                keyValueModel.Key = "amountOrj";
                keyValueModel.Value = this.value.replace(",", ".");
                $.ajax({
                    type: "POST",
                    url: "/VPos/Payment/SetTempData",
                    contentType: "application/json; charset=utf-8",
                    data: JSON.stringify(keyValueModel),
                    dataType: "json",
                    success: function (response) {
                        var amorjValue = response.Value;
                        $("#amorj").val(amorjValue);
                        _onblur_number(amorjValue);
                        //$("#paybuttontext").text($("#inputAmount").val());
                    },
                    error: function (xhr, status, error) {
                        console.log("xhr.status: " + xhr.status + " xhr.statusText: " + xhr.statusText + " status: " + status + " error: " + error);
                    }
                });
            });
        }
    });
</script>
<!-- DCC İşlemleri -->
<script crossorigin="anonymous" type="text/javascript">
    var showcurrency = "";

    function getCurrencySetData(x) {
        $("#DISPENSE_CURRENCY").val(x.DISPENSE_CURRENCY);
        $("#CURRENCY_CODE_NUMERIC").val(x.CURRENCY_CODE_NUMERIC);
        $("#DISPENSE_AMOUNT").val(x.DISPENSE_AMOUNT);
        $("#EXCHANGE_RATE").val(x.EXCHANGE_RATE);
        $("#DCC_CURRENCY_PARITY").val(x.DCC_CURRENCY_PARITY);
        $("#SALE_AMOUNT").val(x.SALE_AMOUNT);
        $("#MARKUP_RATE").val(x.MARKUP_RATE);
        $("#CURRENCY_CODE_ALPHA").val(x.CURRENCY_CODE_ALPHA);
        $("#CURRENCY_PARITY").val(x.CURRENCY_PARITY);
        $("#ForeignPaymenAllowData").show();
    }

    function getCurrencyData(sel) {
        if (sel.value === "") {
            var x = JSON.parse('{"DISPENSE_CURRENCY":"","CURRENCY_CODE_NUMERIC":"","DISPENSE_AMOUNT":"","EXCHANGE_RATE":"","DCC_CURRENCY_PARITY":"","SALE_AMOUNT":"","MARKUP_RATE":"","CURRENCY_CODE_ALPHA":"","CURRENCY_PARITY":""}');
            getCurrencySetData(x);
            $("#ForeignPaymenAllowData").hide();
            return;
        }
        var x = JSON.parse(sel.value);
        if (!IsNullOrEmty(showcurrency))
            $(showcurrency).css("display", "none");
        showcurrency = "#detay_" + x.CURRENCY_CODE_NUMERIC;
        $(showcurrency).css("display", "block");
        getCurrencySetData(x);
        $("#ForeignPaymenAllowData").show();
        $("#paybuttontext").prop("disabled", false);
        $("#paybuttontext").text($("#CURRENCY_CODE_ALPHA").val() + " " + $("#SALE_AMOUNT").val() + " Öde");
        if ($("#cpaybuttontext") != undefined) {
            $("#cpaybuttontext").prop("disabled", false);
        }
        if ($("#registerbtn") != undefined) {
            $("#registerbtn").prop("disabled", false);
        }
    }
</script>
<script>
    var isCardStorageList = false;
    isCardStorageList = 'False' == 'True';
    $(document).ready(function () {
        if (isCardStorageList) {
            console.log("refresh");
            $("#cardStoragelist").css("display", "");
            $("#index").css("display", "none");
        }
    });
    $('#cardStoragecheck').change(function () {
        if ($(this).is(":checked")) {
            if (isCardStorageList) {
                $("#cardStoragelist").css("display", "");
                $("#index").css("display", "none");
            } else {
                $("#cardStoragebutton, #cardStorageHeader,#accountAliasNameDiv").show();
                $("#cardStoragechxdiv, #indexHeader, #indexbutton, #cardProgram").hide();
                $("#CardStorageRegister").val("true");
            }
            return;
        }
        $("#cardStoragebutton, #cardStorageHeader, #accountAliasNameDiv").hide();
        $("#cardStoragechxdiv, #indexHeader, #indexbutton, #cardProgram").show();
        $("#CardStorageRegister").val("false");
    });
    $("#backIndexc").on("click", function () {
        if (isCardStorageList) {
            console.log("refresh 1");
            $("#cardStoragelist").css("display", "");
            $("#index").css("display", "none");
        } else {
            $("#cardStoragebutton, #cardStorageHeader, #accountAliasNameDiv").hide();
            $("#cardStoragechxdiv, #indexHeader, #indexbutton, #cardProgram").show();
            $("#CardStorageRegister").val("false");
            document.getElementById("cardStoragecheck").checked = false;
        }
    });
</script>
</main>