(function() {
    /**
     * 测试(首次从 URL 获取数据)
     */

    initTest();

    function initTest() {
        $("#test").bsSuggest('init', {
            /*url: "/rest/sys/getuserlist?keyword=",
            effectiveFields: ["userName", "email"],
            searchFields: [ "shortAccount"],
            effectiveFieldsAlias:{userName: "姓名"},*/
            clearable: true,
            url: "/data.json",
            idField: "userId",
            keyField: "userName"
        }).on('onDataRequestSuccess', function (e, result) {
            console.log('onDataRequestSuccess: ', result);
        }).on('onSetSelectValue', function (e, keyword, data) {
            console.log('onSetSelectValue: ', keyword, data);
        }).on('onUnsetSelectValue', function () {
            console.log('onUnsetSelectValue');
        }).on('onShowDropdown', function (e, data) {
            console.log('onShowDropdown', e.target.value, data);
        }).on('onHideDropdown', function (e, data) {
            console.log('onHideDropdown', e.target.value, data);
        });
    }



    /**
     * 测试(modal 中显示；不自动选中值；不显示按钮)
     */



    /**
     * 从 data参数中过滤数据
     */
    var dataList = {value: []}, i = 5001;
    while(i--) {
        dataList.value.push({
            id: i,
            word: Math.random() * 100000,
            description: 'http://lzw.me'
        });
    }
    $("#test_data").bsSuggest({
        indexId: 2,  //data.value 的第几个数据，作为input输入框的内容
        indexKey: 1, //data.value 的第几个数据，作为input输入框的内容
        data: dataList
    }).on('onDataRequestSuccess', function (e, result) {
        console.log('从 json.data 参数中获取，不会触发 onDataRequestSuccess 事件', result);
    }).on('onSetSelectValue', function (e, keyword, data) {
        console.log('onSetSelectValue: ', keyword, data);
    }).on('onUnsetSelectValue', function () {
        console.log("onUnsetSelectValue");
    });











    //版本切换
    $('#bsVersion button').on('click', function() {
        var ver = $(this).data('version');
        var cdnSite = '//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/';
        // var cdnSite = '//stackpath.bootstrapcdn.com/bootstrap/';
        $('#bscss').attr('href', cdnSite + ver + '/css/bootstrap.min.css');
        $('#bsjs').attr('src', cdnSite + ver + '/js/bootstrap.min.js');
    });
}());
