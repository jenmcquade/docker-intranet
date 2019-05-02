import React from 'react';
import { connect } from 'react-redux';
import Styles from './menus/Menu.styles';
import CubeStyles from './3d/rubix/Cube.styles';
import { SEARCH_RETURN_COUNT, toggleHistoryPanel } from '../modules/InstaProxy/InstaProxyActions';
import 'html-gl/dist/htmlgl.min';
import Draggable from 'react-draggable';

// Create container styles
const styles = new Styles();
const cubeStyles = new CubeStyles();
const IgHistoryWrapper = styles.igHistory;
const Side = styles.igHistorySide;
const Content = styles.igHistoryContent;
const ItemImage = cubeStyles.itemImage;
const PagingWrapper = styles.pagingWrapper;
const Icon = styles.icon;
const DraggableHandle = cubeStyles.handle;

const HistoryIcon = (props) => { 
  return <ItemImage style={props.style} active={props.active}/>;
}

const PageIcon = (props) => { 
  let fontSize = props.active ? '2em' : '1em';
  return <Icon style={{fontSize: fontSize}} active={props.active} className="fa fa-circle"></Icon>;
}

class IgHistory extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      ...props,
      igHistoryIsOpen: false,
    }
    this.toggleIgHistory = toggleIgHistory.bind(this);
    this.getPagingWrapperPosition = getPagingWrapperPosition.bind(this);
    this.getPageIcons = getPageIcons.bind(this);
    this.getPagePosts = getPagePosts.bind(this);
    this.handleHistoryDrag = handleHistoryDrag.bind(this);
  }

  render() {
    let PageIcons = this.getPageIcons(this.props.ig.payloadHistory);
    let PagePosts = this.getPagePosts(this.props.ig.payloadHistory);
    let translateX = this.getPagingWrapperPosition(this.props.ig.payloadHistory);
    return(
      <IgHistoryWrapper active={this.props.ig.historyPanelIsOpen}>
        <PagingWrapper style={{transform: 'translateX(' + translateX + '%) translateY(-5em)'}} onClick={this.toggleIgHistory}>
          {PageIcons.map((icon) => {
            return icon;
          })}
        </PagingWrapper>
        <Side id="wrap-left"/>
        <Content>
          <Draggable
            axis="x"
            handle=".handle"
            defaultPosition={{x: 0, y: 0}}
            position={null}
            grid={[5, 5]}
            onDrag={this.handleHistoryDrag}
          >
            <DraggableHandle className="handle" style={{top: '0em', left: '2em', display: 'flex'}}>
              <html-gl style={{display: 'flex'}}>
                {PagePosts.map((post) => {
                  return post;
                })}
              </html-gl>
            </DraggableHandle>
          </Draggable>
        </Content>
        <Side id="wrap-right"/>
      </IgHistoryWrapper>
    );
  }
}

function getPagePosts(payloadHistory) {
  let pagesCount = getPagesCount(payloadHistory);
  if(pagesCount < 1) {
    return [];
  }
  let total = 0;
  let images = [];
  let returnCount = payloadHistory.length;

  for(let i=0; i < returnCount; i++) {
    total += payloadHistory[i].length;
    for(let post in payloadHistory[i]) {
      images.push(payloadHistory[i][post].node.thumbnail_resources[0].src);
    }
  }

  let PagePosts = [];
  for(let i=0; i < total; i++) {
    let active = i === 0 ? true : false;
    let backgroundImage = 'url(' + images[i] + ')';
    PagePosts.push(
      <HistoryIcon 
        style={{ 
          opacity: .9,
          margin: '0.5em', 
          transform: 'scale(0.7)', 
          backgroundImage: backgroundImage 
        }} 
        active={active} key={i}>
      </HistoryIcon>
    );
  }
  return PagePosts;  
}

function getPageIcons(payloadHistory) {
  let pagesCount = getPagesCount(payloadHistory);
  let pageTotal = pagesCount < SEARCH_RETURN_COUNT ? pagesCount: SEARCH_RETURN_COUNT; 
  let PageIcons = [];
  for(let i=0; i < pageTotal; i++) {
    let active = i === 0 ? true : false;
    PageIcons.push(<PageIcon active={active} key={i}/>);
  }
  return PageIcons;
}

function getPagingWrapperPosition(payloadHistory) {
  let pagesCount = getPagesCount(payloadHistory);
  let pageTotal = pagesCount < SEARCH_RETURN_COUNT ? pagesCount: SEARCH_RETURN_COUNT; 
  let translateX = 50;
  if(pageTotal < SEARCH_RETURN_COUNT) {
    for(let i=0; i < pagesCount; i++) {
      translateX -= 2.2;
    }
  } 
  if(pageTotal === SEARCH_RETURN_COUNT) {
    translateX = 28;
  }
  return translateX;
}

function getPagesCount(payloadHistory) {
  let total = 0;
  let historySections = payloadHistory.length;
    for(let i=0; i < historySections; i++) {
      total += payloadHistory[i];
    }
  return parseInt(total / SEARCH_RETURN_COUNT, 10);
}

function toggleIgHistory() {
  this.props.dispatch(toggleHistoryPanel());
}

function handleHistoryDrag(e, data) {
  console.log(data);
}

// Retrieve data from store as props
function mapStateToProps(store) {
  return {
    router: store.routerReducer,
    ig: store.instaProxy,
  };
}

export default connect(mapStateToProps)(IgHistory);
