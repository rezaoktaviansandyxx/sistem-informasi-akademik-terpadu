import 'dart:html' as html;

void triggerBrowserDownloadImpl(String url) {
  html.AnchorElement(href: url)
    ..target = '_blank'
    ..click();
}
