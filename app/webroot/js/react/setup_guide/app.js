import ReactDOM from 'react-dom'
import Routes, { unlisten } from './config/routes'

ReactDOM.render(<Routes />, document.getElementById("setup-guide-app"));

// Stop to listen browzer history changing
unlisten()
