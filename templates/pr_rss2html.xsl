<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:xd="http://www.oxygenxml.com/ns/doc/xsl"
  xmlns:content="http://purl.org/rss/1.0/modules/content/"
  xmlns:wfw="http://wellformedweb.org/CommentAPI/"
  xmlns:dc="http://purl.org/dc/elements/1.1/"
  xmlns:atom="http://www.w3.org/2005/Atom"
  xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
  xmlns:slash="http://purl.org/rss/1.0/modules/slash/" version="1.0">
  <xd:doc scope="stylesheet">
    <xd:desc>
      <xd:p><xd:b>Created on:</xd:b> Jul 27, 2010</xd:p>
      <xd:p><xd:b>Author:</xd:b> Patrick Rashleigh</xd:p>
      <xd:p>A sample stylesheet to transform RSS to HTML</xd:p>
    </xd:desc>
  </xd:doc>
  <xsl:output method="xml" media-type="text/html" encoding="UTF-16"/>
  <xsl:template match="/">
    <html>
      <head>
        <title>
          <xsl:value-of select="/rss/channel/title"/>
        </title>
        <link rel="stylesheet" href="pr_rss2html.css"/>
      </head>
      <body>
        <h1>
          <xsl:value-of select="/rss/channel/title"/>
        </h1>
        <p class="book-description">
          <xsl:value-of select="/rss/channel/description"/>
        </p>
        <xsl:for-each select="/rss/channel/item">
          <h2>
            <xsl:value-of select="title"/>
          </h2>
          <p class="post-description">&#8594; Source: <span style="font-family: monospace"><xsl:value-of
                select="link"/></span>, published on <xsl:value-of
              select="pubDate"/> by <xsl:value-of select="dc:creator"/>. </p>
          <div class="post-content">
            <!--
            <xsl:value-of select="substring(content:encoded, 2)"
              disable-output-escaping="yes"/> -->
            <xsl:value-of select="content:encoded"
              disable-output-escaping="yes"/>
          </div>
        </xsl:for-each>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>
