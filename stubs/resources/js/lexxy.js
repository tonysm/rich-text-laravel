import * as Lexxy from "@37signals/lexxy"

// Rich Text Laravel uses a custom attachment tag name, so we must configure it here...
Lexxy.configure({ global: { attachmentTagName: "rich-text-attachment" } })

export default Lexxy;
