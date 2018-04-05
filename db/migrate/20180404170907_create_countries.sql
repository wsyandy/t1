create table countries (
  id serial PRIMARY key not null,
  code VARCHAR(255),
  english_name VARCHAR(255),
  chinese_name VARCHAR(255),
  status INTEGER ,
  image VARCHAR (255),
  rank int
);

create index english_name_on_countries on countries(english_name);
create index chinese_name_on_countries on countries(chinese_name);
create index code_on_countries on countries(code);
create index rank_on_countries on countries(rank);

insert into countries(code, english_name, chinese_name, rank) values ('US', 'United States', '美国', 1);
insert into countries(code, english_name, chinese_name, rank) values ('JP', 'Japan', '日本', 2);
insert into countries(code, english_name, chinese_name, rank) values ('KR', 'Korea  South', '韩国', 3);
insert into countries(code, english_name, chinese_name, rank) values ('DE', 'Germany', '德国', 4);
insert into countries(code, english_name, chinese_name, rank) values ('GB', 'United Kingdom', '英国', 5);
insert into countries(code, english_name, chinese_name, rank) values ('FR', 'France', '法国', 6);
insert into countries(code, english_name, chinese_name, rank) values ('IT', 'Italy', '意大利', 7);
insert into countries(code, english_name, chinese_name, rank) values ('RU', 'Russian Federation', '俄罗斯联邦', 8);
insert into countries(code, english_name, chinese_name, rank) values ('IN', 'India', '印度', 9);
insert into countries(code, english_name, chinese_name, rank) values ('BR', 'Brazil', '巴西', 10);
insert into countries(code, english_name, chinese_name, rank) values ('AU', 'Australia', '澳大利亚', 11);
insert into countries(code, english_name, chinese_name, rank) values ('ES', 'Spain', '西班牙', 12);

insert into countries(code, english_name, chinese_name) values ('HK', 'Hong Kong', '香港');
insert into countries(code, english_name, chinese_name) values ('MO', 'Macau', '澳门');
insert into countries(code, english_name, chinese_name) values ('KR', 'Korea  South', '韩国');
insert into countries(code, english_name, chinese_name) values ('TW', 'Taiwan', '台湾');
insert into countries(code, english_name, chinese_name) values ('ID', 'Indonesia', '印度尼西亚');
insert into countries(code, english_name, chinese_name) values ('JP', 'Japan', '日本');
insert into countries(code, english_name, chinese_name) values ('MY', 'Malaysia', '马来西亚');
insert into countries(code, english_name, chinese_name) values ('PH', 'Philippines', '菲律宾');
insert into countries(code, english_name, chinese_name) values ('SG', 'Singapore', '新加坡');
insert into countries(code, english_name, chinese_name) values ('TH', 'Thailand', '泰国');
insert into countries(code, english_name, chinese_name) values ('VN', 'Vietnam', '越南');
insert into countries(code, english_name, chinese_name) values ('AU', 'Australia', '澳大利亚');
insert into countries(code, english_name, chinese_name) values ('NZ', 'New Zealand', '新西兰');
insert into countries(code, english_name, chinese_name) values ('CA', 'Canada', '加拿大');
insert into countries(code, english_name, chinese_name) values ('US', 'United States', '美国');
insert into countries(code, english_name, chinese_name) values ('BE', 'Belgium', '比利时');
insert into countries(code, english_name, chinese_name) values ('DE', 'Germany', '德国');
insert into countries(code, english_name, chinese_name) values ('DK', 'Denmark', '丹麦');
insert into countries(code, english_name, chinese_name) values ('ES', 'Spain', '西班牙');
insert into countries(code, english_name, chinese_name) values ('FI', 'Finland', '芬兰');
insert into countries(code, english_name, chinese_name) values ('FR', 'France', '法国');
insert into countries(code, english_name, chinese_name) values ('GB', 'United Kingdom', '英国');
insert into countries(code, english_name, chinese_name) values ('GR', 'Greece', '希腊');
insert into countries(code, english_name, chinese_name) values ('IE', 'Ireland', '爱尔兰');
insert into countries(code, english_name, chinese_name) values ('IT', 'Italy', '意大利');
insert into countries(code, english_name, chinese_name) values ('LU', 'Luxembourg', '卢森堡');
insert into countries(code, english_name, chinese_name) values ('MT', 'Malta', '马耳他');
insert into countries(code, english_name, chinese_name) values ('NL', 'Netherlands', '荷兰');
insert into countries(code, english_name, chinese_name) values ('PT', 'Portugal', '葡萄牙');
insert into countries(code, english_name, chinese_name) values ('SE', 'Sweden', '瑞典');
insert into countries(code, english_name, chinese_name) values ('TR', 'Turkey', '土耳其');
insert into countries(code, english_name, chinese_name) values ('AT', 'Austria', '奥地利');
insert into countries(code, english_name, chinese_name) values ('BG', 'Bulgaria', '保加利亚');
insert into countries(code, english_name, chinese_name) values ('CH', 'Switzerland', '瑞士');
insert into countries(code, english_name, chinese_name) values ('CY', 'Cyprus', '塞浦路斯');
insert into countries(code, english_name, chinese_name) values ('CZ', 'Czech Republic', '捷克');
insert into countries(code, english_name, chinese_name) values ('EE', 'Estonia', '爱沙尼亚');
insert into countries(code, english_name, chinese_name) values ('HR', 'Croatia', '克罗地亚');
insert into countries(code, english_name, chinese_name) values ('HU', 'Hungary', '匈牙利');
insert into countries(code, english_name, chinese_name) values ('LT', 'Lithuania', '立陶宛');
insert into countries(code, english_name, chinese_name) values ('LV', 'Latvia', '拉脱维亚');
insert into countries(code, english_name, chinese_name) values ('NO', 'Norway', '挪威');
insert into countries(code, english_name, chinese_name) values ('PL', 'Poland', '波兰');
insert into countries(code, english_name, chinese_name) values ('RO', 'Romania', '罗马尼亚');
insert into countries(code, english_name, chinese_name) values ('SI', 'Slovenia', '斯洛文尼亚');
insert into countries(code, english_name, chinese_name) values ('SK', 'Slovakia', '斯洛伐克');
insert into countries(code, english_name, chinese_name) values ('GL', 'Greenland', '格陵兰岛');
insert into countries(code, english_name, chinese_name) values ('LI', 'Liechtenstein', '列支敦士登');
insert into countries(code, english_name, chinese_name) values ('MC', 'Monaco', '摩纳哥');
insert into countries(code, english_name, chinese_name) values ('SM', 'San Marino', '圣马力诺');
insert into countries(code, english_name, chinese_name) values ('ZA', 'South Africa', '南非');
insert into countries(code, english_name, chinese_name) values ('AL', 'Albania', '阿尔巴尼亚');
insert into countries(code, english_name, chinese_name) values ('AM', 'Armenia', '亚美尼亚');
insert into countries(code, english_name, chinese_name) values ('AZ', 'Azerbaijan', '阿塞拜疆');
insert into countries(code, english_name, chinese_name) values ('BA', 'Bosnia Herzegovina', '波斯尼亚和黑塞哥维亚');
insert into countries(code, english_name, chinese_name) values ('BY', 'Belarus', '伯拉鲁斯');
insert into countries(code, english_name, chinese_name) values ('CS', 'Serbia', '塞尔维亚');
insert into countries(code, english_name, chinese_name) values ('GE', 'Georgia', '格鲁吉亚');
insert into countries(code, english_name, chinese_name) values ('IS', 'Iceland', '冰岛');
insert into countries(code, english_name, chinese_name) values ('MD', 'Moldova', '摩尔多瓦');
insert into countries(code, english_name, chinese_name) values ('MK', 'Macedonia', '马其顿');
insert into countries(code, english_name, chinese_name) values ('UA', 'Ukraine', '乌克兰');
insert into countries(code, english_name, chinese_name) values ('YK', 'Kosovo', '科索沃');
insert into countries(code, english_name, chinese_name) values ('YM', 'Montenegro', '黑山共和国');
insert into countries(code, english_name, chinese_name) values ('BD', 'Bangladesh', '孟加拉');
insert into countries(code, english_name, chinese_name) values ('IN', 'India', '印度');
insert into countries(code, english_name, chinese_name) values ('LK', 'Sri Lanka', '斯里兰卡');
insert into countries(code, english_name, chinese_name) values ('NP', 'Nepal', '尼泊尔');
insert into countries(code, english_name, chinese_name) values ('PK', 'Pakistan', '巴基斯坦');
insert into countries(code, english_name, chinese_name) values ('YE', 'Yemen', '也门');
insert into countries(code, english_name, chinese_name) values ('AE', 'United Arab Emirates', '阿联酋');
insert into countries(code, english_name, chinese_name) values ('BH', 'Bahrain', '巴林');
insert into countries(code, english_name, chinese_name) values ('EG', 'Egypt', '埃及');
insert into countries(code, english_name, chinese_name) values ('IL', 'Israel', '以色列');
insert into countries(code, english_name, chinese_name) values ('IQ', 'Iraq', '伊拉克');
insert into countries(code, english_name, chinese_name) values ('IR', 'Iran', '伊朗');
insert into countries(code, english_name, chinese_name) values ('JO', 'Jordan', '约旦');
insert into countries(code, english_name, chinese_name) values ('KW', 'Kuwait', '科威特');
insert into countries(code, english_name, chinese_name) values ('LB', 'Lebanon', '黎巴嫩');
insert into countries(code, english_name, chinese_name) values ('OM', 'Oman', '阿曼');
insert into countries(code, english_name, chinese_name) values ('PS', 'Palestine', '巴勒斯坦');
insert into countries(code, english_name, chinese_name) values ('QA', 'Qatar', '卡塔尔');
insert into countries(code, english_name, chinese_name) values ('SA', 'Saudi Arabia', '沙特阿拉伯');
insert into countries(code, english_name, chinese_name) values ('SY', 'Syria', '叙利亚');
insert into countries(code, english_name, chinese_name) values ('RU', 'Russian Federation', '俄罗斯联邦');
insert into countries(code, english_name, chinese_name) values ('BN', 'Brunei Darussalam', '文莱');
insert into countries(code, english_name, chinese_name) values ('KH', 'Cambodia', '柬埔寨');
insert into countries(code, english_name, chinese_name) values ('LA', 'Lao People S Dem  Republic', '老挝');
insert into countries(code, english_name, chinese_name) values ('FJ', 'Fiji', '斐济群岛');
insert into countries(code, english_name, chinese_name) values ('PG', 'Papua New Guinea', '巴布亚新几内亚');
insert into countries(code, english_name, chinese_name) values ('AF', 'Afghanistan', '阿富汗');
insert into countries(code, english_name, chinese_name) values ('MM', 'Myanmar', '缅甸');
insert into countries(code, english_name, chinese_name) values ('AO', 'Angola', '安哥拉');
insert into countries(code, english_name, chinese_name) values ('BF', 'Burkina Faso', '布基纳法索');
insert into countries(code, english_name, chinese_name) values ('BI', 'Burundi', '布隆迪');
insert into countries(code, english_name, chinese_name) values ('BJ', 'Benin', '贝宁');
insert into countries(code, english_name, chinese_name) values ('BT', 'Bhutan', '不丹');
insert into countries(code, english_name, chinese_name) values ('BW', 'Botswana', '博茨瓦纳');
insert into countries(code, english_name, chinese_name) values ('CF', 'Central African Republic', '中非共和国');
insert into countries(code, english_name, chinese_name) values ('CG', 'Republic of Congo', '刚果共和国');
insert into countries(code, english_name, chinese_name) values ('CI', 'Cote D Ivoire', '科特迪瓦');
insert into countries(code, english_name, chinese_name) values ('CM', 'Cameroon', '喀麦隆');
insert into countries(code, english_name, chinese_name) values ('CV', 'Cape Verde', '佛得角');
insert into countries(code, english_name, chinese_name) values ('DJ', 'Djibouti', '吉布提');
insert into countries(code, english_name, chinese_name) values ('DZ', 'Algeria', '阿尔及利亚');
insert into countries(code, english_name, chinese_name) values ('ER', 'Eritrea', '厄立特里亚');
insert into countries(code, english_name, chinese_name) values ('ET', 'Ethiopia', '埃塞俄比亚');
insert into countries(code, english_name, chinese_name) values ('GA', 'Gabon', '加蓬');
insert into countries(code, english_name, chinese_name) values ('GH', 'Ghana', '加纳');
insert into countries(code, english_name, chinese_name) values ('GM', 'Gambia', '冈比亚');
insert into countries(code, english_name, chinese_name) values ('GN', 'Guinea', '几内亚');
insert into countries(code, english_name, chinese_name) values ('GQ', 'Equatorial Guinea', '赤道几内亚');
insert into countries(code, english_name, chinese_name) values ('GW', 'Guinea Bissau', '几内亚比绍');
insert into countries(code, english_name, chinese_name) values ('KE', 'Kenya', '肯尼亚');
insert into countries(code, english_name, chinese_name) values ('KM', 'Comoros Islands', '科麽罗');
insert into countries(code, english_name, chinese_name) values ('LR', 'Liberia', '利比里亚');
insert into countries(code, english_name, chinese_name) values ('LS', 'Lesotho', '莱索托');
insert into countries(code, english_name, chinese_name) values ('LY', 'Libya', '利比亚');
insert into countries(code, english_name, chinese_name) values ('MA', 'Morocco', '摩洛哥');
insert into countries(code, english_name, chinese_name) values ('MG', 'Madagascar', '马达加斯加');
insert into countries(code, english_name, chinese_name) values ('ML', 'Mali', '马里');
insert into countries(code, english_name, chinese_name) values ('MR', 'Mauritania', '毛里塔尼亚');
insert into countries(code, english_name, chinese_name) values ('MU', 'Mauritius', '毛里求斯');
insert into countries(code, english_name, chinese_name) values ('MW', 'Malawi', '马拉维');
insert into countries(code, english_name, chinese_name) values ('MZ', 'Mozambique', '莫桑比克');
insert into countries(code, english_name, chinese_name) values ('NA', 'Namibia', '纳米比亚');
insert into countries(code, english_name, chinese_name) values ('NE', 'Niger', '尼日尔');
insert into countries(code, english_name, chinese_name) values ('NG', 'Nigeria', '尼日利亚');
insert into countries(code, english_name, chinese_name) values ('RE', 'Reunion', '留尼汪岛');
insert into countries(code, english_name, chinese_name) values ('RW', 'Rwanda', '卢旺达');
insert into countries(code, english_name, chinese_name) values ('SC', 'Seychelles', '塞舌尔');
insert into countries(code, english_name, chinese_name) values ('SD', 'Sudan', '苏丹');
insert into countries(code, english_name, chinese_name) values ('SL', 'Sierra Leone', '塞拉利昂');
insert into countries(code, english_name, chinese_name) values ('SN', 'Senegal', '塞内加尔');
insert into countries(code, english_name, chinese_name) values ('SO', 'Somalia', '索马里');
insert into countries(code, english_name, chinese_name) values ('ST', 'Sao Tome And Principe', '圣多美和普林西比');
insert into countries(code, english_name, chinese_name) values ('SZ', 'Swaziland', '斯威士兰');
insert into countries(code, english_name, chinese_name) values ('TD', 'Chad', '乍得');
insert into countries(code, english_name, chinese_name) values ('TG', 'Togo', '多哥');
insert into countries(code, english_name, chinese_name) values ('TN', 'Tunisia', '突尼斯');
insert into countries(code, english_name, chinese_name) values ('TZ', 'Tanzania', '坦桑尼亚');
insert into countries(code, english_name, chinese_name) values ('UG', 'Uganda', '乌干达');
insert into countries(code, english_name, chinese_name) values ('YT', 'Mayotte', '马约群岛');
insert into countries(code, english_name, chinese_name) values ('ZM', 'Zambia', '赞比亚');
insert into countries(code, english_name, chinese_name) values ('ZW', 'Zimbabwe', '津巴布韦');
insert into countries(code, english_name, chinese_name) values ('KG', 'Kyrgyzstan', '吉尔吉斯斯坦');
insert into countries(code, english_name, chinese_name) values ('KP', 'Korea  North', '朝鲜');
insert into countries(code, english_name, chinese_name) values ('KZ', 'Kazakhstan', '哈萨克斯坦');
insert into countries(code, english_name, chinese_name) values ('MN', 'Mongolia', '蒙古');
insert into countries(code, english_name, chinese_name) values ('TJ', 'Tajikistan', '塔吉克斯坦');
insert into countries(code, english_name, chinese_name) values ('TM', 'Turkmenistan', '土库曼斯坦');
insert into countries(code, english_name, chinese_name) values ('UZ', 'Uzbekistan', '乌兹别克斯坦');
insert into countries(code, english_name, chinese_name) values ('AG', 'Antigua And Barbuda', '安提瓜');
insert into countries(code, english_name, chinese_name) values ('AI', 'Anguilla', '安圭拉');
insert into countries(code, english_name, chinese_name) values ('AN', 'Netherlands Antilles', '荷属安提尔群岛');
insert into countries(code, english_name, chinese_name) values ('AW', 'Aruba', '阿鲁巴');
insert into countries(code, english_name, chinese_name) values ('BB', 'Barbados', '巴巴多斯');
insert into countries(code, english_name, chinese_name) values ('BM', 'Bermuda', '百慕大');
insert into countries(code, english_name, chinese_name) values ('BS', 'Bahamas', '巴哈马');
insert into countries(code, english_name, chinese_name) values ('JM', 'Jamaica', '牙买加');
insert into countries(code, english_name, chinese_name) values ('KY', 'Cayman Islands', '开曼群岛');
insert into countries(code, english_name, chinese_name) values ('LC', 'Saint Lucia', '圣卢西亚岛');
insert into countries(code, english_name, chinese_name) values ('MQ', 'Martinique', '马提尼克');
insert into countries(code, english_name, chinese_name) values ('TC', 'Turks And Caicos Islands', '特克斯和凯科斯群岛');
insert into countries(code, english_name, chinese_name) values ('TT', 'Trinidad', '特立尼达');
insert into countries(code, english_name, chinese_name) values ('VG', 'Virgin Islands  British', '英属维尔京群岛');
insert into countries(code, english_name, chinese_name) values ('VI', 'Virgin Islands  U S', '美属维尔京群岛');
insert into countries(code, english_name, chinese_name) values ('BZ', 'Belize', '伯利兹');
insert into countries(code, english_name, chinese_name) values ('CR', 'Costa Rica', '哥斯达黎加');
insert into countries(code, english_name, chinese_name) values ('CU', 'Cuba', '古巴');
insert into countries(code, english_name, chinese_name) values ('DM', 'Dominica', '多米尼加');
insert into countries(code, english_name, chinese_name) values ('DO', 'Dominican Rep  Santo Domingo', '多米尼加共和国');
insert into countries(code, english_name, chinese_name) values ('GD', 'Grenada', '格林纳达');
insert into countries(code, english_name, chinese_name) values ('GP', 'Guadeloupe', '瓜得罗普');
insert into countries(code, english_name, chinese_name) values ('GT', 'Guatemala', '危地马拉');
insert into countries(code, english_name, chinese_name) values ('HN', 'Honduras', '洪都拉斯');
insert into countries(code, english_name, chinese_name) values ('HT', 'Haiti', '海地');
insert into countries(code, english_name, chinese_name) values ('MS', 'Montserrat', '蒙特塞拉岛');
insert into countries(code, english_name, chinese_name) values ('MX', 'Mexico', '墨西哥');
insert into countries(code, english_name, chinese_name) values ('NI', 'Nicaragua', '尼加拉瓜');
insert into countries(code, english_name, chinese_name) values ('PA', 'Panama', '巴拿马');
insert into countries(code, english_name, chinese_name) values ('PR', 'Puerto Rico', '波多黎各');
insert into countries(code, english_name, chinese_name) values ('SV', 'El Salvador', '塞尔瓦多');
insert into countries(code, english_name, chinese_name) values ('AD', 'Andorra', '安多拉');
insert into countries(code, english_name, chinese_name) values ('FO', 'Faroe Islands', '法鲁岛');
insert into countries(code, english_name, chinese_name) values ('GI', 'Gibraltar', '直布罗陀');
insert into countries(code, english_name, chinese_name) values ('MV', 'Maldives', '马尔代夫');
insert into countries(code, english_name, chinese_name) values ('PM', 'Saint Pierre And Miquelon', '圣皮埃尔和密克隆');
insert into countries(code, english_name, chinese_name) values ('AS', 'American Samoa', '美属萨摩亚');
insert into countries(code, english_name, chinese_name) values ('CK', 'Cook Islands', '库克群岛');
insert into countries(code, english_name, chinese_name) values ('FM', 'Micronesia', '密克罗尼西亚');
insert into countries(code, english_name, chinese_name) values ('GU', 'Guam', '关岛');
insert into countries(code, english_name, chinese_name) values ('KI', 'Kiribati', '吉列巴提');
insert into countries(code, english_name, chinese_name) values ('MH', 'Marshall Islands', '马绍尔群岛');
insert into countries(code, english_name, chinese_name) values ('MP', 'Commonwealth No. Mariana Islands ', '北马利亚纳群岛');
insert into countries(code, english_name, chinese_name) values ('NC', 'New Caledonia', '新喀里多尼亚');
insert into countries(code, english_name, chinese_name) values ('NF', 'Norfolk Island', '诺夫克群岛');
insert into countries(code, english_name, chinese_name) values ('NR', 'Nauru', '拿鲁岛');
insert into countries(code, english_name, chinese_name) values ('PF', 'French Polynesia', '法属利尼西亚');
insert into countries(code, english_name, chinese_name) values ('PW', 'Palau', '帕劳 ');
insert into countries(code, english_name, chinese_name) values ('SB', 'Solomon Islands', '所罗门群岛');
insert into countries(code, english_name, chinese_name) values ('TO', 'Tonga', '汤加');
insert into countries(code, english_name, chinese_name) values ('TV', 'Tuvalu', '图瓦卢');
insert into countries(code, english_name, chinese_name) values ('VU', 'Vanuatu', '瓦努阿图');
insert into countries(code, english_name, chinese_name) values ('WF', 'Wallis And Futuna Islands', '瓦利斯群岛和富图纳群岛（法属）');
insert into countries(code, english_name, chinese_name) values ('WS', 'Samoa', '萨摩亚群岛');
insert into countries(code, english_name, chinese_name) values ('AR', 'Argentina', '阿根廷');
insert into countries(code, english_name, chinese_name) values ('BO', 'Bolivia', '玻利维亚');
insert into countries(code, english_name, chinese_name) values ('BR', 'Brazil', '巴西');
insert into countries(code, english_name, chinese_name) values ('CL', 'Chile', '智利');
insert into countries(code, english_name, chinese_name) values ('CO', 'Colombia', '哥伦比亚');
insert into countries(code, english_name, chinese_name) values ('EC', 'Ecuador', '厄瓜多尔');
insert into countries(code, english_name, chinese_name) values ('GF', 'French Guiana', '法属圭亚那');
insert into countries(code, english_name, chinese_name) values ('GY', 'Guyana', '圭亚那');
insert into countries(code, english_name, chinese_name) values ('PE', 'Peru', '秘鲁');
insert into countries(code, english_name, chinese_name) values ('PY', 'Paraguay', '巴拉圭');
insert into countries(code, english_name, chinese_name) values ('SR', 'Suriname', '苏里南');
insert into countries(code, english_name, chinese_name) values ('UY', 'Uruguay', '乌拉圭');
insert into countries(code, english_name, chinese_name) values ('VE', 'Venezuela', '委内瑞拉');
insert into countries(code, english_name, chinese_name) values ('CD', 'Democratic Republic of Congo', '刚果民主共和国');
insert into countries(code, english_name, chinese_name) values ('VC', 'St. Vincent and the Grenadines', '圣文森特岛');
insert into countries(code, english_name, chinese_name) values ('CW', 'Curacao', '库拉索');
insert into countries(code, english_name, chinese_name) values ('CE', 'Coate d''Ivoire', '科特迪瓦');