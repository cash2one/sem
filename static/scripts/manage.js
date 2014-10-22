/*
 * search_manage
 * 搜索管理页面左侧导航及表格tab请求
 */
$(function () {
    Base.init();
    sem.leftSwitch();
    $(".nav1").addClass("active");

    S.getStat({url: '/user/statistics', container: 'summary'});
    S.getListData({url: '/plan/list', container: 'plan_list', tpl: 'planListTpl'});

    $('#nav_tabs').click(function (e) {
        var elem = e.target;
        e.preventDefault();
        if (elem.nodeName != 'A' && /active/g.test(elem.parentNode.className)) return;

        var cur_tab = elem.href.replace(/^.*#/, '');
        $(this).find('li').removeClass('active');
        $('#tab_content > div').hide();
        elem.parentNode.className += 'active';
        $('#' + cur_tab).show();
        switch (cur_tab) {
            case 'portlet_tab1':
                S.getListData({url: '/plan/list', container: 'plan_list', page: 1, page_size: 10, tpl: 'planListTpl'});
                break;
            case 'portlet_tab2':
                S.getListData({url: '/unit/list', container: 'unit_list', page: 1, page_size: 10, tpl: 'unitListTpl'});
                break;
            case 'portlet_tab3':
                S.getListData({url: '/keyword/feed', container: 'keyword_list', page: 1, page_size: 10, tpl: 'keywordListTpl'});
                break;
            case 'portlet_tab4':
                S.getListData({url: '/creative/feed', container: 'creative_list', page: 1, page_size: 10, tpl: 'creativeListTpl'});
                break;
        }

    });

    //搜索管理左侧导航事件
    $('#levelTree').click(function (e) {
        e.preventDefault();
        var elem = e.target;
        if (elem.nodeName == 'I') {
            $(elem).toggleClass("closed").parents('p').next().toggle();
        }
        if (elem.nodeName != 'A') return;
        var type = elem.getAttribute('type').replace('tree-', ''), data = elem.getAttribute('data'), index = elem.getAttribute('index');
        $(this).find('li').removeClass('on');
        $(this).find('p').removeClass('on');
        $(elem).parent().addClass('on');

        switch (type) {
            case 'user':
                sem.userId = data;
                sem.userIndex = index;
                sem.planId = '';
                sem.planIndex = '';
                sem.unitId = '';
                sem.unitIndex = '';
                $($('#nav_tabs').find('li')[0]).show();
                $($('#nav_tabs').find('li')[1]).show();
                $('#portlet_tab1').show();
                $('#portlet_tab2').show();
                $($('#nav_tabs').find('li')[0]).find('a').click();
                $("#port-plan,#port-unit").hide();
                sem.getStat({url: '/user/statistics', container: 'summary', type: "user"});
                break;
            case 'plan':
                sem.userId = $(elem).parents('li.user').find('p>a').attr('data');
                sem.userIndex = $(elem).parents('li.user').find('p>a').attr('index');
                sem.planId = data;
                sem.planIndex = index;
                sem.unitId = '';
                sem.unitIndex = '';
                $($('#nav_tabs').find('li')[0]).hide();
                $($('#nav_tabs').find('li')[1]).show();
                $('#portlet_tab1').hide();
                $('#portlet_tab2').show();
                $($('#nav_tabs').find('li')[1]).find('a').click();

                $("#port-plan").show();
                $("#port-unit").hide();
                sem.getStat({url: '/plan/stat', container: 'summary', type: "plan", "plan_id": sem.planId});
                break;
            case 'unit':
                sem.unitId = data;
                sem.unitIndex = index;
                sem.userId = $(elem).parents('li.user').find('p>a').attr('data');
                sem.userIndex = $(elem).parents('li.user').find('p>a').attr('index');
                sem.planId = $(elem).parents('li.plan').find('p>a').attr('data');
                sem.planIndex = $(elem).parents('li.plan').find('p>a').attr('index');
                $($('#nav_tabs').find('li')[0]).hide();
                $($('#nav_tabs').find('li')[1]).hide();
                $('#portlet_tab1').hide();
                $('#portlet_tab2').hide();
                $($('#nav_tabs').find('li')[2]).find('a').click();

                $("#port-plan").hide();
                $("#port-unit").show();
                sem.getStat({url: '/unit/stat', container: 'summary', type: "unit", "unit_id": sem.unitId});
                break;
        }
        $.cookie("tree_type", type);//点击是账户|计划|单元
        $.cookie("tree_id", $(elem).attr("data"));//当前点击tree id
        balanceHeight();
    });
});
