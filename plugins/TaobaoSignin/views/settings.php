<?php if (!defined('APPLICATION')) exit();
?>
<style type="text/css">
.Configuration {
   margin: 0 20px 20px;
   background: #f5f5f5;
   float: left;
}
.ConfigurationForm {
   padding: 20px;
   float: left;
}
#Content form .ConfigurationForm ul {
   padding: 0;
}
#Content form .ConfigurationForm input.Button {
   margin: 0;
}
.ConfigurationHelp {
   border-left: 1px solid #aaa;
   margin-left: 340px;
   padding: 20px;
}
.ConfigurationHelp strong {
    display: block;
}
.ConfigurationHelp img {
   width: 99%;
}
.ConfigurationHelp a img {
    border: 1px solid #aaa;
}
.ConfigurationHelp a:hover img {
    border: 1px solid #777;
}
input.CopyInput {
   font-family: monospace;
   color: #000;
   width: 240px;
   font-size: 12px;
   padding: 4px 3px;
}
</style>
<h1><?php echo $this->Data('Title'); ?></h1>
<?php
echo $this->Form->Open();
echo $this->Form->Errors();
?>
<div class="Info">
   <?php echo T('使用户可以使用淘宝账户登录', '使用户可以使用淘宝账户登录 <b>你需要在淘宝开放平台注册才能使用</b>'); ?>
</div>
<div class="Configuration">
   <div class="ConfigurationForm">
      <ul>
         <li>
            <?php
               echo $this->Form->Label('App Key', 'AppKey');
               echo $this->Form->TextBox('AppKey');
            ?>
         </li>
         <li>
            <?php
               echo $this->Form->Label('App Secret', 'Secret');
               echo $this->Form->TextBox('Secret');
            ?>
         </li>
      </ul>
      <?php echo $this->Form->Button('Save', array('class' => 'Button SliceSubmit')); ?>
   </div>
   <div class="ConfigurationHelp">
      <strong>获取AppKey和AppSecret的方式</strong>
      <p>将你的网站在<a href="http://open.taobao.com/xtao" target="_blank">http://open.taobao.com/xtao</a>注册</p>
      <p>获取到AppKey和AppSecret后点击保存</p>
   </div>
</div>   
<?php
   echo $this->Form->Close();
