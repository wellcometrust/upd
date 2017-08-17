CKEDITOR.plugins.add( 'tooltip', {
  icons: 'tooltip',
  init: function( editor ) {
    editor.addCommand( 'tooltip', new CKEDITOR.dialogCommand( 'tooltipDialog' ) );
    editor.ui.addButton( 'tooltip', {
      label: 'Insert tooltip',
      command: 'tooltip',
      toolbar: 'insert'
    });

    CKEDITOR.dialog.add( 'tooltipDialog', this.path + 'dialogs/tooltip.js' );
  }
});