CKEDITOR.dialog.add( 'tooltipDialog', function( editor ) {
  return {
    title: 'tooltip Properties',
    minWidth: 400,
    minHeight: 200,
    contents: [
      {
        id: 'tab-basic',
        label: 'Basic Settings',
        elements: [
          {
            type: 'text',
            id: 'word',
            label: 'Text',
            validate: CKEDITOR.dialog.validate.notEmpty( "Text cannot be empty." ),
            setup: function( element ) {
              this.setValue( element.getText() );
            },
            commit: function( element ) {
              element.setText( this.getValue() );
            }
          },
          {
            type: 'text',
            id: 'tooltip',
            label: 'Explanation',
            validate: CKEDITOR.dialog.validate.notEmpty( "Explanation cannot be empty." ),
            setup: function( element ) {
              this.setValue( element.getAttribute( "tooltip" ) );
            },
            commit: function( element ) {
              element.setAttribute( "tooltip", this.getValue() );
            }
          }
        ]
      }
    ],
    onShow: function() {
      var selection = editor.getSelection();
      var element = selection.getStartElement();

      if ( element ) {
        element = element.getAscendant( 'span', true );
        newElement = element
      }

      if ( !element || !element.hasAttribute("tooltip")){
        newElement = editor.document.createElement('span');
        newElement.appendText(selection.getSelectedText());
        this.insertMode = true;
      }
      else {
        this.insertMode = false;
      }

      this.element = newElement;
      this.setupContent( this.element );
    },

    onOk: function() {
      var tooltip = this.element;
      this.commitContent( tooltip );

      if ( this.insertMode ) {
        editor.insertElement( tooltip );
      }
    }
  };
});