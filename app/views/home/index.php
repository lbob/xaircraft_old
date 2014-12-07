hello_<?php echo $testHere ?>
<form id="test" action="http://localhost:84/" method="POST">
    <?php echo $this->html()->textBox('test_name'); ?>
    <?php echo $this->html()->submit(); ?>
</form>
<?php echo $this->html()->link('/post/edit', 'ddd', array('idd' => 333), 'like'); ?>