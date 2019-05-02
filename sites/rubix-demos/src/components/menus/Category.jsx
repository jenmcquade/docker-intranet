/**
 * This Component is applied
 *  to menu categories in their individual
 *  constructors to build menu items with shared functionality
 * See this file and Common.js for logic applied to all menu items
 */

import React, { Component } from 'react';
import { connect } from 'react-redux';

//
// Import Styled Components and React Bootstrap Components
//
import Common, {
  Item,
  Icon,
  Title,
  Trigger,
  Content,
  CategoryLabel,
} from '../menus/Common';

import { toggleMenu } from './MenuActions';

class Category extends Component {
  /**
   * Constructor
   * 
   * 1. Merge new Common object with this
   * 2. Set the menu color theme
   * 3. Set state to Redux store
   * 4. Local properties and bindings
   * 
   * @param {*} props 
   */
  constructor(props) {
    super(props);
    Object.assign(this, new Common(this)); // Import common functions as methods
    this.id = props.id.toLowerCase();
    this.setScreenTheme(props, true); // See Common.js
    let route = window.location.pathname;
    this.triggerUrl = route.indexOf('/' + this.id + '/') !== -1 ? 
      '/' + window.location.search + window.location.hash : 
      '/' + this.id + window.location.search + window.location.hash

    // If we loaded the app with this menu item listed in the url, open the item on load
    this.openOnLoad = route.split('/')[1] && route.split('/')[1].toLowerCase() === this.id ? true : false;
    
    // Menu id and theming
    this.category = this.props.menu.categories[this.id];
    this.themeColor = this.category.backgroundColor; 
    this.triggerColor = this.category.triggerColor;

    this.state = {
      ...props,
      isDefaultState: true,
      themeColor: this.themeColor,
      triggerColor: this.triggerColor,
    }
  }

  componentDidMount() {
    let route = window.location.pathname;
    if(route.split('/')[1] && route.split('/')[1].toLowerCase() === this.id) {
      this.props.dispatch(toggleMenu(this.id, true));
    }
  }
 
  //
  // Lifecycle handlers
  //
  componentWillReceiveProps(nextProps) {
    this.updateUrl(nextProps);
    this.updateMenuState(nextProps);
  }

  componentDidUpdate() {
    // Re-theme when dimensions change
    if(this.props.id) {
      this.setScreenTheme(this.props); // See Common.js
      document.querySelector('#' + this.props.id + ' a').style.backgroundColor = this.themeColor;
      document.querySelector('#' + this.props.id + ' a').style['color'] = this.triggerColor;
    }
  }

  //
  // Render to the Menu container
  //
  render() {
    if(!this.props.id) {
      return false;
    }
    let category = this.state.menu.categories[this.id];
    let label = this.props.label;
    let baseColor = this.category.baseColor;
    let menuIsOpen = category.isDefaultState && this.openOnLoad ? true : this.category.menuIsOpen; 
    let iconType = this.props.iconType;
    let inlineContentTransform = this.category.inlineContentTransform;
    return (
      <Item id={this.id} style={{backgroundColor: this.themeColor}}>
        <Trigger 
          default={category.isDefaultState.toString()}
          openonload={this.openOnLoad.toString()}
          active={menuIsOpen.toString()}
          onClick={this.handleTrigger}
          id={this.id + '-trigger'}
          to={this.triggerUrl}
        >
          <Icon className={iconType} />
          <CategoryLabel>{label}</CategoryLabel>
        </Trigger>
        <Content
          scrollable={this.props.id === 'theme' ? true : false}
          default={category.isDefaultState.toString()}
          openonload={this.openOnLoad.toString()}
          active={menuIsOpen.toString()} 
          backgroundColor={baseColor}
          style={{ 
            transform: inlineContentTransform,
          }}
        >
          <Title>{label}</Title> 
          { this.props.children }
        </Content>
      </Item>
    );
  }
}

// Retrieve data from store as props
function mapStateToProps(store, ownProps) {
  return {
    menu: store.menu,
  };
}

export default connect(mapStateToProps)(Category);