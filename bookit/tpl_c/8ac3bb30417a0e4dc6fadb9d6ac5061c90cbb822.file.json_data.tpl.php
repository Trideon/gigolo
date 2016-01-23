<?php /* Smarty version Smarty-3.1.16, created on 2015-04-11 07:26:01
         compiled from "/home/gmagigolo/public_html/bookit/tpl/json_data.tpl" */ ?>
<?php /*%%SmartyHeaderCode:17081126415528cc897532b6-95317439%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '8ac3bb30417a0e4dc6fadb9d6ac5061c90cbb822' => 
    array (
      0 => '/home/gmagigolo/public_html/bookit/tpl/json_data.tpl',
      1 => 1426887478,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '17081126415528cc897532b6-95317439',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'data' => 0,
    'error' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.16',
  'unifunc' => 'content_5528cc89781736_03024990',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5528cc89781736_03024990')) {function content_5528cc89781736_03024990($_smarty_tpl) {?>
<?php if ($_smarty_tpl->tpl_vars['data']->value!='') {?>
<?php echo $_smarty_tpl->tpl_vars['data']->value;?>

<?php }?>
<?php if ($_smarty_tpl->tpl_vars['error']->value!='') {?>
<?php echo $_smarty_tpl->tpl_vars['error']->value;?>

<?php }?><?php }} ?>
