<?php
include('./lib/settings.php');
session_start([
    "gc_maxlifetime"=> 60 * 60 * 24 * 7,
    "cookie_lifetime"=> 60 * 60 * 24 * 7
]);
if(!array_key_exists('host', $_SESSION))
    header("Location: ./login.php");

?>
    <!doctype html>
    <html style="height: 100%;">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title><?php echo $PAGE_TITLE_SQL;?></title>
        <link rel="shortcut icon" href="<?php echo $PAGE_ICON;?>">
        <link rel="stylesheet" href="./lib/jquery-ui/jquery-ui.min.css">
        <link rel="stylesheet" href="./lib/codemirror/5.41.0/codemirror.min.css">
        <link rel="stylesheet" href="./lib/codemirror/5.41.0/addon/hint/show-hint.min.css">
        <link rel="stylesheet" href="./lib/codemirror/5.41.0/theme/3024-day.min.css">
        <link rel="stylesheet" href="./lib/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="./lib/datatables/1.10.19/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="./lib/css.css">

        <script src="./lib/jquery.min.js"></script>
        <script src="./lib/datatables/1.10.19/js/jquery.dataTables.min.js"></script>
    </head>
    <style>
        .block-operate {
            clear: both;
            display: inline-block;
            font-size: 14px;
            cursor: pointer;
            margin-left: 30px;
        }
        .block-operate i {
            margin: 0 6px 0 6px;
        }

        .CodeMirror {
            cursor: text;
            font-size: 16px;
        }
        .closeBlock {
            cursor: pointer;
        }
        .closeBlock:hover {
            color: #5e5e5e;
        }
        .closeBlock:active {
            color: #c5c5c5;
        }
    </style>
    <body>

    <div id="common">
        <div id="msg" v-show="isMsgShow" style="display: none;" :class="{'msgShow':isMsgShow, 'errorMsg':isShowError, 'successMsg': isShowSuccess}">
            <span id="msg-body">{{ message }}</span>
            <span id="msg-close" style="cursor: pointer;" @click="hideMsg">X</span>
        </div>
        <div id="loader" style="display: none;" v-show="isShowLoader"></div>
        <div id="fullScreen" v-show="isShowClose" style="display: none;"></div>
        <div id="close" v-show="isShowClose" style="display: none">
            <div class="close-head">确定离开？<span title="关闭" @click="hideClose">X</span></div>
            <div class="close-body">
                <button @click="logout">确定</button>
                <button @click="hideClose">取消</button>
            </div>
        </div>
        <div class="head">
            <a href="./" id="nav-home">
                <img src="./res/mysql.png"  class="icon home icon-inactive">
                <img src="./res/mysql_active.png"  class="icon home icon-active">
                &nbsp;概述
            </a>
            <a href="./database.php" id="nav-database">
                <img src="./res/database.png"  class="icon database icon-inactive" >
                <img src="./res/database_active.png"  class="icon database icon-active">
                &nbsp;数据库
            </a>
            <a href="./new-delete-table.php" id="nav-table">
                <img src="./res/table.png"  class="icon table icon-inactive">
                <img src="./res/table_active.png"  class="icon table icon-active">
                &nbsp;数据表
            </a>
            <a href="./sql.php" id="nav-sql" class="active">
                <img src="./res/sql.png"  class="icon sql icon-inactive" >
                <img src="./res/sql_active.png"  class="icon sql icon-active">
                &nbsp;SQL
            </a>
            <a href="./toolbox.php" id="nav-backup">
                <img src="./res/backup.png"  class="icon backup icon-inactive" >
                <img src="./res/backup_active.png"  class="icon backup icon-active">
                &nbsp;工具箱
            </a>
            <span id="exit" title="退出" @click="showClose">X</span>
        </div>
    </div>


    <div class="main" style="margin: 80px 20px 0 20px; width: unset;">
        <div class="block" id="editor">
            <div class="block-head">
                SQL 查询
                <div class="block-operate">
                    <i class="fa fa-undo" title="撤销" id="editor-undo"></i>
                    <i class="fa fa-repeat" title="恢复" id="editor-redo"></i>
                    <i class="fa fa-unlock" title="锁定" id="editor-lock"></i>
                    <a href="javascript:void(0)" style="text-decoration: underline dotted;" class="toggleBody">收起&nbsp;↑</a>
                </div>
            </div>
            <div class="block-body" style="text-align: unset;padding-bottom: 0" id="editor-body">
                <textarea name="sql-editor" id="sql-editor" title="sql 输入"></textarea>
                <div id="operate" style="text-align: center">
                    <button id="operate-submit">&nbsp;&nbsp;提交&nbsp;&nbsp;</button>
                    <button id="operate-clear">&nbsp;&nbsp;清空&nbsp;&nbsp;</button>
                </div>
            </div>
        </div>
    </div>

    <script src="./lib/codemirror/5.41.0/codemirror.min.js"></script>
    <script src="./lib/codemirror/5.41.0/mode/sql/sql.min.js"></script>
    <script src="./lib/codemirror/5.41.0/addon/hint/show-hint.min.js"></script>
    <script src="./lib/codemirror/5.41.0/addon/hint/sql-hint.min.js"></script>
    <script src="./lib/vue.min.js"></script>
    <script src="./lib/axios.min.js"></script>
    <script src="./lib/js.js"></script>

    <script>

        $(document).ready(function() {
            setTimeout(function () {

                $("#editor-lock").click(function () {
                    if($(this).hasClass("fa-lock")) {
                        $(this).removeClass("fa-lock").addClass("fa-unlock").parent().find("a").unbind("click").removeClass("lock-toggleBody").addClass("toggleBody");
                    } else {
                        $(this).removeClass("fa-unlock").addClass("fa-lock").parent().find("a").unbind("click").removeClass("toggleBody").addClass("lock-toggleBody");
                    }
                });
                var editor = CodeMirror.fromTextArea(document.getElementById("sql-editor"),{
                    lineNumbers: true,
                    mode: {name: "text/x-mysql"},
                    extraKeys: {"Alt": "autocomplete"},
                    theme: "3024-day",
                    autofocus: true
                });
                $("#operate-submit").click(function () {

                    let sql = editor.getValue().replace(/\t|\r|\n/g," ").split(";").filter(function (el) {
                        return el !== "" && el.length !==0;
                    });
                    if(sql.length === 0)
                        common.showError("无输入！");
                    else {
                        $.ajax({
                            url: './lib/Processing.php',
                            method: "post",
                            data: {'type':"5","sql":sql},
                            beforeSend: function(){
                                common.showLoader();
                            },
                            complete: function() {
                                common.hideLoader();
                            },
                            dataType: "json",
                            success: function (data) {
                                if(data.success) {
                                    if(!$("#editor-lock").hasClass("fa-lock"))
                                        $("#editor .block-body").slideUp(1000,function () {
                                            if($(this).is(":hidden"))
                                                $(this).prev("div").css("border-radius","10px").find(".toggleBody").html("展开&nbsp;↓");
                                            else
                                                $(this).prev("div").css("border-radius","10px 10px 0 0").find(".toggleBody").html("收起&nbsp;↑");
                                        });
                                    $("#editor").nextAll().slideUp(500,function () {
                                        $(this).remove();
                                    });
                                    data.msg.forEach(function (each) {
                                        $(".main").append(each);
                                    });
                                } else
                                    common.showError(data.msg);
                            }
                        })
                    }
                }).next().click(function () {
                    editor.setValue("");
                });
                $("#editor-undo").click(function () {
                    editor.undo();
                }).next().click(function () {
                    editor.redo();
                });
                setInterval(function () {
                    $(".toggleBody").unbind("click").click(function () {
                        $(this).parent().parent().next().slideToggle(1000,function () {
                            if($(this).is(":hidden"))
                                $(this).prev("div").css("border-radius","10px").find(".toggleBody").html("展开&nbsp;↓");
                            else
                                $(this).prev("div").css("border-radius","10px 10px 0 0").find(".toggleBody").html("收起&nbsp;↑");
                        })
                    });
                    $(".lock-toggleBody").click(function () {
                        common.showError("收起/展开 编辑框需要先解锁！");
                    });
                    $(".closeBlock").click(function () {
                        $(this).parent().parent().slideUp(300,function () {
                            $(this).remove();
                        });
                    });
                },100);
            }, 500)
        });
    </script>
    </body>
    </html>