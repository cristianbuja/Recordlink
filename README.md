# Recordlink

### Important notice for version 2.0.1

Obsolete extension

### Important notice for version 2.x.x

TS Config changes example:
- See example in Configuration/PageTS

Query for update from 1.3.0 version
- See update script


### Important notice for version 1.3.0
This version is a bridge for upgrade from TYPO3 7.6 to 8 LTS

- Install this version before upgrade your CMS 
- Perform query manualy. You can find some example running update script
- Upgrade TYPO3 to 8 LTS 
- Update Recordlink to next version 2.0.0 

Query for update link fields: 
- UPDATE [table] SET [field] = REPLACE([field], 'record:[key]', 'recordlink:[key]') WHERE 1=1

Query for update rte fields: 
- UPDATE [table] SET [field] = REPLACE([field], '<link record:[key]', '<link recordlink:[key]') WHERE 1=1

Example Query for tt_content and sys_file_reference for example key "category": 
- UPDATE tt_content SET header_link = REPLACE(header_link, 'record:category', 'recordlink:category') WHERE 1=1;
- UPDATE tt_content SET bodytext = REPLACE(bodytext, '<link record:category', '<link recordlink:category') WHERE 1=1;
- UPDATE sys_file_reference SET link = REPLACE(link, 'record:category', 'recordlink:category') WHERE 1=1;
