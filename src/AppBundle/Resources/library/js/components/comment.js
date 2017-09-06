React.createClass({ displayName: 'ComponentComment',
  /**
   *
   */
  getDefaultProps: function() {
    return {
      body: '',
      createdDate: null,
      displayName: null,
      picture: null,
      approved: false,
    };
  },

  /**
   *
   */
  getInitialState: function() {
    return {
      settings : Application.settings.posts,
    }
  },

  /**
   *
   */
  render: function() {
    var postedBy = 'Posted by ' + this.props.displayName || 'Unknown';
    if (!this.props.approved) {
      postedBy = postedBy + ' (Pending Moderation)';
    }
    var picture = this.props.picture || this.state.settings.defaultPicture;

    return (
      React.createElement('div', { className: 'comment' },
        React.createElement('div', { className: 'left' },
          React.createElement('img', { className: 'media-img', src: picture, alt: 'Avatar' })
        ),
        React.createElement('div', { className: 'right' },
          React.createElement('div', { className: 'attribution muted' },
            React.createElement('span', { className: 'date' },
              React.createElement('date', null, this.props.createdDate)
            ),
            React.createElement('span', null, postedBy)
          ),
          React.createElement('p', { className: 'comment-body' }, this.props.body)
        ),
      )
    );
  },
});
