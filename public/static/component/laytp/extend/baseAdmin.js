layui.define(['laytp'],function(exports){
    /**
     * erp基础模块
     * @type {string}
     */
    const MOD_NAME = "baseAdmin";
    const funController = {};
    let $ = layui.jquery;

    //公共参数
    funController.commonData = {
        modalSizeMap : {'s':['50%', '40%'],'m':['70%', '80%'],'l':['90%', '90%']},
        goodsDetailRoute:'/admin/goods/detail.html?id=',
        memberDetailRoute:'/admin/member/edit.html?id=',
        OrderDetailRoute:'/admin/order/edit.html?id=',
    };
    funController.data = {};

    funController.init = function(){};
    funController.initHtml = function(){};
    funController.initEvent = function(){};
    funController.tableRender = function(){};
    funController.initCommon = function(){


        funController.commonModals();
    };
    funController.getGoodsCategory = function(){
        let nowDate = new Date();
        let storageData = localStorage.getItem("goodsCategory")
        storageData = JSON.parse(storageData);
        //2个小时更新
        if(!storageData || nowDate.getTime()>storageData.ttl+1000*3600*2){
            facade.ajax({
                route: '/admin.common/goodsCategory',
                data: {is_tree:1},
                async: false,
                successAlert: false,
            }).done(function (res) {
                if (res.code === 0) {
                    let goodsCategory = res.data;
                    let storageData = {
                        goodsCategory:goodsCategory,
                        ttl: nowDate.getTime()
                    };
                    localStorage.setItem("goodsCategory",JSON.stringify(storageData));
                }
            })
        }
        return JSON.stringify(storageData.goodsCategory);
    };
    funController.executeSearchForm = function(where={}){
        $("#search-form")[0].reset();
        layui.form.val('search-form', where);
        $("#search-btn").trigger("click");
    };
    funController.submitAjax = function(route,data,btnAnim,callback){
        facade.ajax({
            route:route,
            data:data,
            successAlert:true
        }).done(function(res){
            if (typeof callback === "function") {
                callback(res);
            }else{
                if(res.code === 0){
                    setTimeout(function () {
                        parent.layui.layer.close(parent.layui.layer.getFrameIndex(window.name));//关闭当前页
                        parent.funController.tableRender();
                    },500)
                }
            }
            btnAnim.stop();
        }).fail(function(){
            btnAnim.stop();
        });
    };
    funController.actionAjax = function(route,data,callback){
        facade.ajax({
            route: route,
            data: data,
        }).done(function (res) {
            if (typeof callback === "function") {
                callback(res);
            }else{
                if (res.code === 0) {
                    funController.tableRender();
                }
            }
        })
    };
    funController.actionSyncAjax = function(route,data,callback){
        facade.ajax({
            route: route,
            data: data,
            async:false,
            successAlert:false,
        }).done(function (res) {
            if (typeof callback === "function") {
                callback(res);
            }else{
                if (res.code === 0) {
                    funController.tableRender();
                }
            }
        })
    };
    funController.bindEvent = function(event,filter,callback){
        $(document).on(event,filter,callback)
    };
    funController.openModal = function(route,title,size='m'){
        layer.open({
            type: 2,
            title: title,
            shadeClose: true,
            shade: 0.8,
            maxmin: true,
            area: funController.commonData.modalSizeMap[size],
            content: route
        });
    };
    funController.commonModals = function(){
        funController.bindEvent('click','.goods-detail', function(data) {
            let goods_id = $(this).data('goods_id');
            funController.openModal(funController.commonData.goodsDetailRoute+goods_id,'商品详情');
        });
        funController.bindEvent('click','.member-detail', function(data) {
            let member_id = $(this).data('member_id');
            funController.openModal(funController.commonData.memberDetailRoute+member_id,'会员详情');
        });
        funController.bindEvent('click', '.order-detail', function(data) {
            let order_id = $(this).data('order_id');
            funController.openModal(funController.commonData.OrderDetailRoute+order_id,'订单详情','l');
        })
    };
    funController.commonTools = function(){
        //复制
        funController.bindEvent('click','.copy-btn', function(data) {
            let text = $(this).data('text');
            console.log(text);const inputo = document.createElement("input");
            document.body.appendChild(inputo);
            inputo.value = text;
            inputo.setAttribute('readOnly', 'readOnly')
            inputo.select();
            document.execCommand("Copy");
            document.body.removeChild(inputo);

            facade.success('复制成功');
        });
    };
    funController.copy = function(text){
        const inputo = document.createElement("input");
        document.body.appendChild(inputo);
        inputo.value = text;
        inputo.setAttribute('readOnly', 'readOnly')
        inputo.select();
        document.execCommand("Copy");
        document.body.removeChild(inputo);

        facade.success('复制成功');
    };
    funController.printBarcode = function(barcode){
        let imgSrc;
        facade.ajax({
            route:'/admin.goods/barcode',
            data:{barcode:barcode},
            successAlert:false,
            async:false,
        }).done(function(res){
            if(res.code==0){
                imgSrc = res.data.barcode;
            }
        })
        let oWin = window.open('', 'pringwindow', 'menubar=no,location=no,resizable=yes,scrollbars=no,status=no,width=1000,height=660')
        oWin.document.fn = function() {
            if (oWin) {
                oWin.print()
                oWin.close()
            }
        }
        let html = '<div style="">' + `<img src="data:image/png;base64,${imgSrc}" onload="fn()"  />` + '</div>'
        oWin.document.open()
        oWin.document.write(html)
        oWin.document.close()
    };

    funController.initCommon();
    exports(MOD_NAME, funController);

    layui.baseFunController = funController;
    window.baseFunController = funController;
});