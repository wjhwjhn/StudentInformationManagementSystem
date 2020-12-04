uploadsarr = Array();
allstudents = Array();
logintid = "";
loginname = "";
test = false;
uploadsurl = test ? "uploads/" :"http://wjh-blog.test.upcdn.net/sign.wjhwjhn.com/uploads/";
init();
getinitinfo();
//初始化函数
function init()
{
    $('.table-bordered').bootstrapTable({
        undefinedText: '无内容',
        formatNoMatches: function formatNoMatches() {
            return '无内容';
        }
    })
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": false,
        "progressBar": false,
        "positionClass": "toast-bottom-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "3000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
    $('.selectpicker').selectpicker({
        noneSelectedText: '未选择',
        noneResultsText: '未搜索到的数据 {0}'
    });
    if( /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ) {
        $('.selectpicker').selectpicker('mobile');
    }
    $('#UploadModal').on('hidden.bs.modal', function () {
        flushuploads();
    });
    $("#uploadsfile").fileinput({
        uploadUrl: "upload.php",
        uploadAsync: true,
        language:'zh',
        allowedFileTypes: ['image']
    });
    $("#uploadsfile").on("fileuploaded", function (event, data, previewId, index) {
        if (data == undefined) toastr.error('文件上传失败！请刷新再试');
        flushuploads();
    });
    $("#headfile").fileinput({
        uploadUrl: "upload.php",
        uploadAsync: true,
        language:'zh',
        allowedFileTypes: ['image']
    });
    $("#headfile").on("fileuploaded", function (event, data, previewId, index) {
        if (data == undefined)
        {
            toastr.error('文件上传失败！！请刷新再试', '头像设置');
            return;
        }
        var key = data.response.initialPreviewConfig[0].key;
        var sid = $('#studentlist').selectpicker('val');
        if (key == null || key === "")
        {
            toastr.error('上传错误，请重新再试', '头像设置');
            return;
        }
        $.ajax({
            type: "GET",
            url: "getinfo.php?method=setphoto&arg=" + key + "&arg2=" + sid,
            dataType:"json",
            success: function (res) {
                if (res.error == 0)
                {
                    if (res.setphoto == null)
                    {
                        toastr.error('设置头像失败！请重新再试', '头像设置');
                        return;
                    }
                    if (res.setphoto.res > 0)
                    {
                        toastr.success('设置头像成功！', '头像设置');
                        getinitinfo();
                    }
                }
            }
        });
        $('#HeadModal').modal('hide');
    });
}
//读取账号信息
function getinitinfo()
{
    $.ajax({
        type: "GET",
        url: "getinfo.php?method=init",
        dataType:"json",
        success: function (res) {
            $('#userzone').empty();
            switch (res.error) {
                case 0:
                    //已登录
                    loginname = res.init.name;
                    logintid = res.init.tid;
                    $('#userzone').append('<li class="dropdown">\n' +
                        '                <a href="#" class="dropdown-toggle" data-toggle="dropdown">\n' +
                        res.init.name +
                        '                     <b class="caret"></b>\n' +
                        '                </a>\n' +
                        '                <ul class="dropdown-menu">\n' +
                        '                    <li><a href="javascript: exitlogin();">退出登录</a></li>\n' +
                        '                </ul>\n' +
                        '            </li>');
                    getstudentslist();
                    break;
                case -1:
                    //未登录
                    $('#userslist').empty();
                    if (res.getteachers == null)
                    {
                        toastr.error("读取登录账号列表出错，请刷新后再试！", "初始化");
                        return;
                    }
                    for (var i = 0; i < res.getteachers.length; i++)
                    {
                        var temp = res.getteachers[i];
                        $('#userslist').append("<option value=" + temp.tid + ">" + temp.name + "</option>");
                    }
                    $('.selectpicker').selectpicker('refresh');
                    $('#userzone').append('<li><a href="#" data-toggle="modal" data-target="#LoginModal"><span class="glyphicon glyphicon-log-in"></span> 登录</a></li>');
                    $('#LoginModal').modal('show');
                    break;
            }
        }
    });
}

//读取班级学生信息
function getstudentslist()
{
    $.ajax({
            type: "GET",
            url: "getinfo.php?method=getteacherstudents",
            dataType:"json",
            success: function (res) {
                if (res.error !== 0)
                {
                    toastr.error('网络错误，错误代码：' + res.error, '学生信息');
                    return;
                }
                if (res.getteacherstudents == null)
                {
                    toastr.error('网络错误，请联系管理员！', '学生信息');
                    return;
                }

                allstudents = res.getteacherstudents;
                classload();
            }
        }
    )
}

function classload()
{
    var templist = Array();
    var choicetype = $('#studentstype').selectpicker('val');
    $('#classlist').empty();
    for (var i = 0; i < allstudents.length; i++)
    {
        var info = allstudents[i];
        if (info.type != choicetype) continue;
        var flag = false;//去重标记变量
        for (var j = 0; j < templist.length; j++)
        {
            if (info.cid == templist[j]) flag = true;
        }
        if (!flag)
        {
            templist.push(info.cid);
            $('#classlist').append("<option value=" + info.cid + ">" + info.classname + "</option>");
        }
    }
    $('.selectpicker').selectpicker('refresh');
    classchange();
}

//选择的班级改变
function classchange()
{
    var flag = false;//标记有无学生
    var choicecid = $('#classlist').selectpicker('val');
    var choicetype = $('#studentstype').selectpicker('val');
    $('#studentlist').empty();
    $('#studentlist').append("<option value=1000>全部学生</option>");
    for (var i = 0; i < allstudents.length; i++)
    {
        var info = allstudents[i];
        if (info.type != choicetype) continue;
        if (info.cid == choicecid)
        {
            flag = true;
            $('#studentlist').append("<option value=" + info.sid + ">" + info.name + "</option>");
        }
    }
    if (!flag) $('#studentlist').empty();
    $('.selectpicker').selectpicker('refresh');
    studentchange();
}

//选择的学生改变
function studentchange()
{
    var choicesid = $('#studentlist').selectpicker('val');
    var choicecid = $('#classlist').selectpicker('val');
    var choicetype = $('#studentstype').selectpicker('val');

    if (choicecid == null || choicesid == null || choicetype == null)
    {
        $('#studentsbase').hide();
        $('#editform').hide();
        $('#seetext').hide();
        return;
    }
    var lessonrows = Array();
    var teachermarkrows = Array();
    var studentname = $("#studentlist").find("option:selected").text();
    var classname = $("#classlist").find("option:selected").text();

    if (choicesid == 1000)
    {
        $('#infoshow').text(classname + "的学习情况记录");
        $('#studentsbase').hide();
        $('#editform').hide();
        $('#seetext').show();
    }
    else
    {
        $('#studentsbaseinfo').text(studentname + "的基本信息");
        $('#editinfo').text(studentname + "的学习情况填写");
        $('#infoshow').text(studentname + "的学习情况记录");
        $('#studentsbase').show();
        $('#editform').show();
        $('#seetext').show();
    }

    var getarg = "";
    if (choicesid == 1000)
    {
        getarg += "method=getclasstudentdata";
        getarg += "&arg=" + choicecid;
        getarg += "&arg3=" + choicetype;
    }
    else
    {
        getarg += "method=getstudentdata";
        getarg += "&arg=" + choicesid;
    }
    getarg += "&arg2=1000";

    $.ajax({
        type: "GET",
        url: "getinfo.php?" + getarg,
        dataType:"json",
        success: function (res) {
            $('#teachermark').bootstrapTable('removeAll');
            if (res.error !== 0)
            {
                toastr.error('网络错误，错误代码：' + res.error, '学生信息');
                return;
            }
            if (res.getstudentdata != null)
            {
                for (var i = 0; i < res.getstudentdata.length; i++)
                {
                    var studenttemp = res.getstudentdata[i];
                    var filehtml = "无附件";
                    var delhtml = "";
                    if (studenttemp.filename !== "")
                    {
                        filenamearr = studenttemp.filename.split("|");
                        uploadurlarr = studenttemp.uploadurl.split("|");
                        for (var j = 0; j < filenamearr.length; j++)
                        {
                            if (uploadurlarr[j] !== "")
                            {
                                filehtml = "<a href=\"";
                                filehtml += uploadsurl;
                                filehtml += uploadurlarr[j];
                                filehtml += "\" target=\"_blank\">";
                                filehtml += filenamearr[j];
                                filehtml += "</a><br>";
                            }
                        }
                    }
                    delhtml = "<button type=\"button\" class=\"btn btn-danger btn-sm\"";
                    if (studenttemp.tname != loginname) delhtml += "disabled=\"disabled\"";
                    delhtml += "onclick='deletetext(";
                    delhtml += studenttemp.fid;
                    delhtml += ")'>删除</button>";
                    teachermarkrows.push({
                        name: studenttemp.sname,
                        teachername: studenttemp.tname,
                        time: studenttemp.timestamp,
                        text: studenttemp.filetext,
                        files: filehtml,
                        del: delhtml
                    });
                }
                $('#teachermark').bootstrapTable('append', teachermarkrows);
                $('#teachermark').bootstrapTable('refresh');
            }

            if (res.getstudentexam != null)
            {
                $('#eaxmtable').bootstrapTable('removeAll');
                for (var i = 0; i < allstudents.length; i++)
                {
                    var temp = allstudents[i];
                    if (temp.sid == choicesid)
                    {
                        if (temp.photourl == null || temp.photourl === "")
                        {
                            $("#headphoto").attr("src", uploadsurl + "nohead.jpg");
                        }
                        else
                        {
                            $("#headphoto").attr("src", uploadsurl + temp.photourl);
                        }
                        break;
                    }
                }
                for (var i = 0; i < res.getstudentexam.length; i++)
                {
                    var eaxmtemp = res.getstudentexam[i];
                    lessonrows.push({
                        kmname: eaxmtemp.kmname,
                        type: eaxmtemp.type,
                        lasttype: eaxmtemp.lasttype,
                        score: eaxmtemp.score === 0 ? "缺考" : eaxmtemp.score,
                        teacher: eaxmtemp.teacher,
                        source: eaxmtemp.source,
                        examtime: eaxmtemp.examtime,
                    });
                }
                $('#eaxmtable').bootstrapTable('append', lessonrows);
                $('#eaxmtable').bootstrapTable('refresh');
            }
        }
    });
}

function flushuploads()
{
    uploadsarr = $('#uploadsfile').data('fileinput').initialPreviewConfig;
    if (uploadsarr.length == 0)
    {
        $('#uploadsinfo').text("未选择任何附件");
    }
    else
    {
        var cnt = 0;
        for (var i = 0; i < uploadsarr.length; i++)
        {
            if (uploadsarr[i] != null && uploadsarr[i].key !== "" && uploadsarr[i].caption !== "")
            {
                cnt++;
            }
        }
        $('#uploadsinfo').text("已成功上传 " + cnt + "个附件");
    }
}

function signtext()
{
    var sid = $('#studentlist').selectpicker('val');
    var tid = logintid;
    var text = $('#maintext')[0].value;
    if (text == "")
    {
        toastr.error('未填写交流内容', '提交错误');
        return;
    }
    if (tid == 0)
    {
        toastr.error('未选择教师', '提交错误');
        return;
    }
    if (sid == 0)
    {
        toastr.error('未选择学生', '提交错误');
        return;
    }
    uploadurl = "";
    filename = "";
    for (var i = 0; i < uploadsarr.length; i++)
    {
        if (uploadsarr[i] != null && uploadsarr[i].key !== "" && uploadsarr[i].caption !== "")
        {
            uploadurl += uploadsarr[i].key + "|";
            filename += uploadsarr[i].caption + "|";
        }
    }
    $.ajax({
        type: "POST",
        url: "getinfo.php?method=signtext",
        data: {
            sid: sid,
            tid: tid,
            text: text,
            filename : filename,
            uploadurl : uploadurl
        },
        dataType:"json",
        success: function (res) {
            if (res.error == 0)
            {
                if (res.signstudentstext == null)
                {
                    toastr.error('交流内容登记失败', '提交错误');
                }
                if (res.signstudentstext.res <= 0)
                {
                    toastr.error('交流内容登记失败', '提交错误');
                }
                else
                {
                    toastr.success('交流内容登记成功', '提交成功');
                    studentchange();
                }
            }
        }
    });
}

function deletetext(fid)
{
    $.ajax({
        type: "GET",
        url: "getinfo.php?method=deletetext&arg=" + fid,
        dataType:"json",
        success: function (res) {
            if (res.error == 0)
            {
                if (res.deletetext == null)
                {
                    toastr.error('删除记录失败', '提交错误');
                }
                if (res.deletetext.res <= 0)
                {
                    toastr.error('删除记录失败', '提交错误');
                }
                else
                {
                    toastr.success('删除记录成功', '提交成功');
                    classchange();
                }
            }
        }
    });
}

function exitlogin()
{
    var keys=document.cookie.match(/[^ =;]+(?=\=)/g);
    if (keys) {
        for (var i =  keys.length; i--;)
            document.cookie = keys[i] + '=0;path=/;expires=' + new Date(0).toUTCString();
        document.cookie = keys[i] + '=0;path=/;domain=' + document.domain + ';expires=' + new Date(0).toUTCString();
        document.cookie = keys[i] + '=0;path=/;domain=baidu.com;expires=' + new Date(0).toUTCString();
    }
    $('#classlist').empty();
    $('#studentlist').empty();
    $('#studentsbaseinfo').text("学生基本信息");
    $('#editinfo').text("学习情况填写");
    $('#infoshow').text("学习情况记录");
    $('#eaxmtable').bootstrapTable('removeAll');
    $('#teachermark').bootstrapTable('removeAll');
    toastr.warning('退出登录后将不能进行任何操作', '退出成功');
    getinitinfo();
}

function modallogin()
{
    $.ajax({
            type: "GET",
            url: "getinfo.php?method=login&arg=" + $('#userslist').selectpicker('val'),
            dataType:"json",
            success: function (res) {
                if (res.error === 0)
                {
                    if (res.login == null)
                    {
                        toastr.error('网络错误，请联系管理员！', '登录错误');
                        return;
                    }
                    else
                    {
                        toastr.success('欢迎回来，' + res.login.name, '登录成功');
                        $('#LoginModal').modal('hide');
                        getinitinfo();
                    }
                }
                else
                {
                    toastr.error('网络错误，错误代码：' + res.error, '登录错误');
                }
            }
        }
    )
}