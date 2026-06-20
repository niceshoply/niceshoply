<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    // Warehouse
    'warehouse'         => '창고',
    'warehouses'        => '창고 목록',
    'warehouse_code'    => '창고 코드',
    'warehouse_name'    => '창고명',
    'code'              => '코드',
    'name'              => '이름',
    'create_warehouse'  => '창고 생성',
    'edit_warehouse'    => '창고 편집',
    'default_warehouse' => '기본 창고',
    'is_default'        => '기본값',
    'priority'          => '우선순위',
    'active'            => '활성',
    'description'       => '설명',
    'contact_name'      => '담당자명',
    'contact_phone'     => '연락처',
    'country'           => '국가',
    'state'             => '시/도',
    'city'              => '시/군/구',
    'address'           => '주소',
    'address_1'         => '주소 1',
    'address_2'         => '주소 2',
    'zipcode'           => '우편번호',
    'phone'             => '전화번호',
    'latitude'          => '위도',
    'longitude'         => '경도',
    'all_warehouses'    => '모든 창고',

    // Stock
    'stock'             => '재고',
    'warehouse_stocks'  => '창고 재고',
    'sku_code'          => 'SKU 코드',
    'quantity'          => '수량',
    'reserved'          => '예약됨',
    'available'         => '사용 가능',
    'low_threshold'     => '재고 부족 임계값',
    'adjust_stock'      => '재고 조정',
    'add_stock'         => '재고 추가',
    'adjust_quantity'   => '수량 조정',
    'stock_adjusted'    => '재고가 성공적으로 조정되었습니다.',
    'adjust_hint'       => '양수는 추가, 음수는 차감.',
    'import_stock'      => '가져오기',
    'export_stock'      => '내보내기',
    'download_template' => '템플릿 다운로드',
    'import_success'    => '가져오기 완료: :success/:total 건 성공.',
    'import_file'       => '파일 선택',
    'import_file_hint'  => '.xlsx, .csv 파일 지원. 열: warehouse_id, sku_code, quantity.',

    // Stock Movements
    'stock_movements' => '재고 이동',
    'type'            => '유형',
    'reference'       => '참조',
    'note'            => '메모',
    'all_types'       => '모든 유형',

    // Stock Transfers
    'stock_transfers' => '재고 이관',
    'transfer_number' => '이관 번호',
    'from_warehouse'  => '출발 창고',
    'to_warehouse'    => '도착 창고',
    'create_transfer' => '이관 생성',
    'transfer_detail' => '이관 상세',
    'items'           => '항목',
    'received'        => '수령됨',
    'ship'            => '배송',
    'complete'        => '완료',
    'status'          => '상태',

    // Shipment
    'packages'          => '패키지',
    'package'           => '패키지',
    'ship_package'      => '패키지 배송',
    'express_company'   => '택배사',
    'express_number'    => '운송장 번호',
    'all_shipped'       => '모든 패키지가 배송되었습니다.',
    'partially_shipped' => '부분 배송됨',

    // Allocation
    'allocation_strategy'   => '할당 전략',
    'strategy_priority'     => '우선순위 기반',
    'strategy_nearest'      => '가장 가까운 창고',
    'strategy_stock_first'  => '재고 우선',
    'strategy_cost_optimal' => '비용 최적화',
    'allow_split_shipment'  => '분할 배송 허용',

    // Settings
    'warehouse_enabled'  => '창고 관리 활성화',
    'warehouse_settings' => '창고 설정',
    'service_areas'      => '서비스 지역',
    'service_area_hint'  => '이 창고가 서비스하는 지역을 정의합니다. 비워두면 전 세계 대응.',
    'all_states'         => '모든 시/도',
];
