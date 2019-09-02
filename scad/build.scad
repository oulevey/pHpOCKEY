include <config.scad>;
include <heatmap.scad>;

// calculate base real size with corners radius
base_offset   = base_radius * 2;
base_width    = grid_cols - base_offset;
base_depth    = grid_rows - base_offset;
base_center_x = grid_cols / 2;
base_center_y = grid_rows / 2;

// create base shape, rectangle with rounded corners
module make_base_plate() {
  xy = base_offset / 2;
  h  = base_height / 2;

  translate([xy, xy, 0]) {
    minkowski() {
      cube([base_width, base_depth, h]);
      cylinder(r = base_radius, h = h);
    }
  }
}

module make_base_border() {
  xy = base_offset / 2;
  h  = base_height / 2;
  o = floor_line_thickness * 2;
  difference() {
    translate([xy - floor_line_thickness, xy - floor_line_thickness, 0]) {
      minkowski() {
        cube([base_width + o, base_depth + o, h + floor_line_thickness - 0.1]);
        cylinder(r = base_radius, h = h + floor_line_thickness);
      }
    }
    translate([0, 0, -1])
      scale([1, 1, 3])
        make_base_plate();
  }
}

make_base_border();

// create an outlined circle
module make_circle(radius, height = 1, thickness = 0.5, center_radius = 0.5) {
  difference() {
    cylinder(r = radius, h = height);
    translate([0, 0, -0.1])
      cylinder(r = radius - thickness, h = height + 0.2);
  }
  if (center_radius) {
    cylinder(r = center_radius, h = height);
  }
}

// create goal half cylinder
module goal() {
  difference() {
    make_circle(goal_radius, center_radius = 0);
    translate([-goal_radius, -goal_radius, 0])
      cube([ goal_radius * 2, goal_radius, floor_line_height + 0.1 ]);
  }
  translate([-base_center_x, -floor_line_thickness + 0.1, 0])
    cube([ grid_cols, floor_line_thickness, floor_line_height + 0.1 ]);
}

// make base plate, with the floor lines
module make_base() {
  make_base_border();
  difference() {
    make_base_plate();
    // z position for all element
    z = base_height - floor_line_height + 0.1;
    // make center circle
    translate([base_center_x, base_center_y, z])
      make_circle(center_circle_radius, height = floor_line_height + 0.1);
    // enter half circle
    translate([ 0, grid_rows / 2, z ])
      make_circle(enter_circle_radius, center_radius = 0);
    // make the 4 angles circles
    translate([ four_circles_x, four_circles_y, z ])
      make_circle(center_circle_radius, height = floor_line_height + 0.1);
    translate([ grid_cols - four_circles_x, four_circles_y, z ])
      make_circle(center_circle_radius, height = floor_line_height + 0.1);
    translate([ four_circles_x, grid_rows - four_circles_y, z ])
      make_circle(center_circle_radius, height = floor_line_height + 0.1);
    translate([ grid_cols - four_circles_x, grid_rows - four_circles_y, z ])
      make_circle(center_circle_radius, height = floor_line_height + 0.1);
    // make the goals
    translate([base_center_x, goal_position, z])
      goal();
    translate([base_center_x, grid_rows - goal_position, z])
      rotate([0, 0, 180])
        goal();
    // make the center line
    offset = floor_line_thickness / 2;
    translate([0, base_center_y - offset, z])
      cube([ grid_cols, floor_line_thickness, floor_line_height + 0.1 ]);
    // make quarter lines
    translate([0, quarter_lines_position, z])
      cube([ grid_cols, floor_line_thickness, floor_line_height + 0.1 ]);
    // make quarter lines
    translate([0, grid_rows - quarter_lines_position, z])
      cube([ grid_cols, floor_line_thickness, floor_line_height + 0.1 ]);
  }
}

make_base();
translate([0, 0, base_height - floor_line_height])
  heatmap(sr = puck_scale_r, sh = puck_scale_h);
