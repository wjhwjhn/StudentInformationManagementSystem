## StudentInformationManagementSystem ##

基于PHP编写的简易学生信息管理系统。
这个项目是为我的高中学校，量身打造的一款定制类的信息交互、表单提交网站。
早期项目作为一个简易的系统，在文件夹**EarlyProject**下，后期使用项目在**students**下。
两者差异较大，但数据库格式相同。
在后者中我采用了**bootstrap**作为一个简易开发的界面引擎，界面短小精悍。

**数据库结构：**
数据库中需要有以下几个表的信息。

1.class
|  cid   |    name     	|
|  ----  |    ----  	|
| 1      | 高三（01）班 |


2.files
| fid | tid | sid | text | timestamp | filename | uploadurl |
| --- | --- | --- | ---- | --------- | -------- | --------- |

3.score
| scid | sid | source | kmname | type | lasttype | score | teacher | examtime   |
| ---- | --- | ------ | ------ | ---- | -------- | ----- | ------- | ---------- |
| 1    | 1   | 联考   | 语文   | 主科 | 主科     | 100.00| 老师    | 2020-01-01 |


4.students
type有0或1两种，代表学生类型
| sid | cid | name | photourl | type |
| --- | --- | ---- | -------- | ---- |
| 1   | 1   | 学生 | Null     | 1    |


5.teachers
| tid | name |
| --- | ---- |
| 1   | 老师 |