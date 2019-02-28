var el = wp.element.createElement,
  registerBlockType = wp.blocks.registerBlockType,
  ServerSideRender = wp.components.ServerSideRender

// Share buttons.
registerBlockType('dev-share-buttons/share', {
  title: 'Share Buttons',
  icon: 'share-alt2',
  category: 'widgets',

  edit: function(props) {
    return el(ServerSideRender, {
      block: 'dev-share-buttons/share',
      attributes: props.attributes,
    })
  },

  save: function() {
    return null
  },
})

// Social profiles.
registerBlockType('dev-share-buttons/profiles', {
  title: 'Social Profile Links',
  icon: 'networking',
  category: 'widgets',

  edit: function(props) {
    return el(ServerSideRender, {
      block: 'dev-share-buttons/profiles',
      attributes: props.attributes,
    })
  },

  save: function() {
    return null
  },
})
