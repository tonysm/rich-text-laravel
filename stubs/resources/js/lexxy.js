import * as Lexxy from "lexxy"

// Rich Text Laravel uses a custom attachment tag name, so we must configure it here...
Lexxy.configure({ global: { attachmentTagName: "rich-text-attachment" } })

window.Trix = Trix;

export default Trix;
